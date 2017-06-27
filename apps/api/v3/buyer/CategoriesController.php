<?php

namespace Application\Api\V3\Buyer;

use Ds\Set;
use Phalcon\Db;

class CategoriesController extends ControllerBase {
	function indexAction() {
		$categories       = [];
		$merchant_ids     = new Set;
		$merchants        = [];
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result           = $this->db->query('SELECT id, name FROM product_categories WHERE user_id ' . ($this->_premium_merchant ? "= {$this->_premium_merchant->id}" : 'IS NULL') . ' AND published = 1 ORDER BY name');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($category = $result->fetch()) {
			$products = [];
			$query    = <<<QUERY
				SELECT
					COUNT(DISTINCT c.id)
				FROM
					users a
					JOIN coverage_area b ON a.id = b.user_id
					JOIN user_product c ON a.id = c.user_id
					JOIN products d ON c.product_id = d.id
				WHERE
					a.status = 1 AND
					b.village_id = {$this->_current_user->village->id} AND
					c.published = 1 AND
					c.stock > 0 AND
					d.published = 1 AND
					a.premium_merchant
QUERY;
			if ($this->_premium_merchant) {
				$query .= " = 1 AND a.id = {$this->_premium_merchant->id}";
			} else {
				$query .= ' IS NULL';
			}
			$query .= " AND d.product_category_id = {$category->id}";
			$total_products = $this->db->fetchColumn($query);
			if (!$total_products) {
				continue;
			}
			$sub_result = $this->db->query(strtr($query, ['COUNT(DISTINCT c.id)' => 'DISTINCT ON (c.id) c.id, c.user_id, d.product_category_id, d.name, c.price, c.stock, d.stock_unit, d.picture']) . ' LIMIT 2 OFFSET 0');
			$sub_result->setFetchMode(Db::FETCH_OBJ);
			while ($product = $sub_result->fetch()) {
				$merchant_ids->contains($product->user_id) || $merchant_ids->add($product->user_id);
				if ($product->picture) {
					$product->thumbnail = $picture_root_url . strtr($product->picture, ['.jpg' => '120.jpg']);
					$product->picture   = $picture_root_url . strtr($product->picture, ['.jpg' => '300.jpg']);
				} else {
					unset($product->picture);
				}
				$products[] = $product;
			}
			$category->products = $products;
			$categories[]       = $category;
		}
		if (!$merchant_ids->isEmpty()) {
			$today    = $this->currentDatetime->format('N');
			$tomorrow = $this->currentDatetime->modify('+1 day')->format('N');
			$query    = <<<QUERY
				SELECT
					DISTINCT
					a.id,
					a.company,
					a.address,
					a.open_on_sunday,
					a.open_on_monday,
					a.open_on_tuesday,
					a.open_on_wednesday,
					a.open_on_thursday,
					a.open_on_friday,
					a.open_on_saturday,
					a.business_opening_hour,
					a.business_closing_hour,
					a.delivery_hours,
					COALESCE(c.minimum_purchase, a.minimum_purchase, d.value::INT) AS minimum_purchase,
					a.shipping_cost,
					a.merchant_note
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN coverage_area c ON a.id = c.user_id
					JOIN settings d ON d.name = 'minimum_purchase'
					JOIN user_product e ON a.id = e.user_id
					JOIN products f ON e.product_id = f.id
					JOIN product_categories g ON f.product_category_id = g.id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
QUERY;
			if ($this->_premium_merchant) {
				$query .= " a.premium_merchant = 1 AND a.id = {$this->_premium_merchant->id}";
			} else {
				$query .= ' a.premium_merchant IS NULL AND a.id IN(' . $merchant_ids->join(',') . ')';
			}
			$query .= ' ORDER BY a.company';
			$result = $this->db->query($query);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$availability = 'Hari ini ';
				if (($today === 1 && $item->open_on_monday) ||
					($today === 2 && $item->open_on_tuesday) ||
					($today === 3 && $item->open_on_wednesday) ||
					($today === 4 && $item->open_on_thursday) ||
					($today === 5 && $item->open_on_friday) ||
					($today === 6 && $item->open_on_saturday) ||
					($today === 7 && $item->open_on_sunday)) {
					$availability .= 'buka';
				} else {
					$availability .= 'tutup';
				}
				$availability .= ', besok ';
				if (($tomorrow === 1 && $item->open_on_monday) ||
					($tomorrow === 2 && $item->open_on_tuesday) ||
					($tomorrow === 3 && $item->open_on_wednesday) ||
					($tomorrow === 4 && $item->open_on_thursday) ||
					($tomorrow === 5 && $item->open_on_friday) ||
					($tomorrow === 6 && $item->open_on_saturday) ||
					($tomorrow === 7 && $item->open_on_sunday)) {
					$availability .= 'buka';
				} else {
					$availability .= 'tutup';
				}
				$business_days = [
					$item->open_on_monday    ? 'Senin'  : ',',
					$item->open_on_tuesday   ? 'Selasa' : ',',
					$item->open_on_wednesday ? 'Rabu'   : ',',
					$item->open_on_thursday  ? 'Kamis'  : ',',
					$item->open_on_friday    ? 'Jumat'  : ',',
					$item->open_on_saturday  ? 'Sabtu'  : ',',
					$item->open_on_sunday    ? 'Minggu' : ',',
				];
				$business_hours = range($item->business_opening_hour, $item->business_closing_hour);
				$hours          = explode(',', $item->delivery_hours);
				if ($hours) {
					foreach ($business_hours as &$hour) {
						if (!in_array($hour, $hours)) {
							$hour = ',';
						} else {
							$hour .= '.00';
						}
					}
				}
				$delivery_hours       = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
				$merchants[$item->id] = [
					'id'               => $item->id,
					'company'          => $item->company,
					'address'          => $item->address,
					'availability'     => $availability,
					'business_days'    => trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-',
					'business_hours'   => $item->business_opening_hour . '.00 - ' . $item->business_closing_hour . '.00 WIB',
					'delivery_hours'   => $delivery_hours ? $delivery_hours . ' WIB' : '-',
					'minimum_purchase' => $item->minimum_purchase,
					'shipping_cost'    => $item->shipping_cost ?? 0,
					'merchant_note'    => $item->merchant_note,
				];
			}
		}
		$this->_response['status']                          = 1;
		$this->_response['data']['categories']              = $categories;
		$this->_response['data']['merchants']               = $merchants;
		$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}