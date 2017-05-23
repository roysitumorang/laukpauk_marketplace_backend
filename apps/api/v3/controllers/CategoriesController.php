<?php

namespace Application\Api\V3\Controllers;

use Phalcon\Db;

class CategoriesController extends ControllerBase {
	function indexAction() {
		$categories       = [];
		$products         = [];
		$merchant_ids     = [];
		$merchants        = [];
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result           = $this->db->query('SELECT id, name FROM product_categories WHERE user_id ' . ($this->_premium_merchant ? "= {$this->_premium_merchant->id}" : 'IS NULL') . ' AND published = 1 ORDER BY name');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$categories[] = $row;
		}
		$query = <<<QUERY
			SELECT
				f.id,
				d.user_id AS merchant_id,
				d.name,
				d.price,
				d.stock,
				d.stock_unit,
				f.picture
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
				JOIN products d ON a.id = d.id
				JOIN product_categories f ON d.product_category_id = f.id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
				d.published = 1 AND
				f.published = 1 AND
				a.premium_merchant
QUERY;
		if ($this->_premium_merchant) {
			$query .= " = 1 AND a.id = {$this->_premium_merchant->id}";
		} else {
			$query .= ' IS NULL';
		}
		$query .= ' ORDER BY RANDOM() LIMIT 10 OFFSET 0';
		$result = $this->db->query($query);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			in_array($row->merchant_id, $merchant_ids) || $merchant_ids[] = $row->merchant_id;
			if ($row->picture) {
				$row->picture = $picture_root_url . strtr($row->picture, ['.jpg' => '120.jpg']);
			} else {
				unset($row->picture);
			}
			if (!$row->order_closing_hour) {
				unset($row->order_closing_hour);
			}
			$products[] = $row;
		}
		if ($merchant_ids) {
			$query = <<<QUERY
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
					a.shipping_cost
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN service_areas c ON a.id = c.user_id
					JOIN settings d ON d.name = 'minimum_purchase'
					JOIN products e ON a.id = e.user_id
					JOIN product_categories f ON e.product_category_id = f.id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
QUERY;
			if ($this->_premium_merchant) {
				$query .= " a.premium_merchant = 1 AND a.id = {$this->_premium_merchant->id}";
			} else {
				$query .= ' a.premium_merchant IS NULL AND a.id IN(' . implode(',', $merchant_ids) . ')';
			}
			$query .= ' ORDER BY a.company';
			$result = $this->db->query($query);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
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
				if ($hours = explode(',', $item->delivery_hours)) {
					foreach ($business_hours as &$hour) {
						if (!in_array($hour, $hours)) {
							$hour = ',';
						} else {
							$hour .= '.00';
						}
					}
				}
				$delivery_hours = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
				$merchant       = [
					'id'                    => $item->id,
					'company'               => $item->company,
					'address'               => $item->address,
					'business_days'         => trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-',
					'business_opening_hour' => $item->business_opening_hour . '.00',
					'business_closing_hour' => $item->business_closing_hour . '.00 WIB',
					'delivery_hours'        => $delivery_hours ? $delivery_hours . ' WIB' : '-',
					'minimum_purchase'      => $item->minimum_purchase,
					'shipping_cost'         => $item->shipping_cost ?? 0,
				];
				$merchants[] = $merchant;
			}
		}
		$this->_response['status'] = 1;
		$this->_response['data']   = [
			'categories' => $categories,
			'products'   => $products,
			'merchants'  => $merchants,
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}