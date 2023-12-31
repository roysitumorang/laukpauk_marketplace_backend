<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Banner;
use Ds\Set;
use Phalcon\Db;

class CategoriesController extends ControllerBase {
	function indexAction() {
		$limit            = $this->request->getServer('HTTP_APP_VERSION') > '2.0.21' ? 8 : 2;
		$categories       = [];
		$sale_packages    = [];
		$merchant_ids     = new Set;
		$merchants        = [];
		$banners          = [];
		$picture_root_url = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/assets/image/';
		foreach (['!', ''] as $condition) {
			$result = $this->db->query("SELECT id, name FROM product_categories WHERE published = 1 AND name {$condition}= 'Lain-Lain' ORDER BY name");
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
						d.product_category_id = {$category->id}
QUERY;
				$total_products = $this->db->fetchColumn($query);
				if (!$total_products) {
					continue;
				}
				$sub_result = $this->db->query(sprintf('SELECT e.* FROM (' . strtr($query, ['COUNT(DISTINCT c.id)' => 'DISTINCT ON (c.id) c.id, c.user_id, d.product_category_id, d.name, c.price, c.stock, d.stock_unit, d.picture']) . ') e ORDER BY RANDOM() LIMIT %d OFFSET 0', $limit));
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
		}
		$result = $this->db->query(sprintf(<<<QUERY
			SELECT
				a.id,
				a.user_id,
				a.name,
				a.price,
				a.stock,
				a.picture,
				STRING_AGG(e.name || ' (' || e.stock_unit || ') x ' || c.quantity, ',') AS products
			FROM
				sale_packages a
				JOIN coverage_area b USING(user_id)
				LEFT JOIN sale_package_product c ON a.id = c.sale_package_id
				LEFT JOIN user_product d ON c.user_product_id = d.id
				LEFT JOIN products e ON d.product_id = e.id
			WHERE
				b.village_id = {$this->_current_user->village->id} AND
				a.published = '1'
			GROUP BY a.id
			ORDER BY RANDOM()
			LIMIT %d OFFSET 0
QUERY
			, $limit
		));
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$merchant_ids->contains($item->user_id) || $merchant_ids->add($item->user_id);
			if ($item->picture) {
				$item->thumbnail = $picture_root_url . strtr($item->picture, ['.jpg' => '120.jpg']);
			}
			$item->products = explode(',', $item->products);
			unset($item->picture);
			$sale_packages[] = $item;
		}
		if (!$merchant_ids->isEmpty()) {
			$today    = lcfirst($this->currentDatetime->format('l'));
			$tomorrow = lcfirst($this->currentDatetime->modify('+1 day')->format('l'));
			$query    = sprintf(<<<QUERY
				SELECT
					DISTINCT
					a.id,
					a.company,
					a.address,
					a.open_on_{$today} AS open_today,
					a.open_on_{$tomorrow} AS open_tomorrow,
					a.business_opening_hour,
					a.business_closing_hour,
					a.delivery_hours,
					a.minimum_purchase,
					c.shipping_cost,
					a.merchant_note
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN coverage_area c ON a.id = c.user_id
					JOIN user_product d ON a.id = d.user_id
					JOIN products e ON d.product_id = e.id
					JOIN product_categories f ON e.product_category_id = f.id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
					a.id IN(%s)
				ORDER BY a.company
QUERY
				, $merchant_ids->join(','));
			$result = $this->db->query($query);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$availability = 'Hari ini ';
				if ($item->open_today && $item->business_closing_hour > $this->currentDatetime->format('G')) {
					$availability .= 'buka';
				} else {
					$availability .= 'tutup';
				}
				$availability .= ', besok ';
				if ($item->open_tomorrow) {
					$availability .= 'buka';
				} else {
					$availability .= 'tutup';
				}
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
					'delivery_hours'   => $delivery_hours ? $delivery_hours . ' WIB' : '-',
					'minimum_purchase' => $item->minimum_purchase,
					'shipping_cost'    => $item->shipping_cost,
					'merchant_note'    => $item->merchant_note,
				];
			}
		}
		foreach (Banner::find(['published = 1', 'columns' => 'file', 'order' => 'id DESC']) as $banner) {
			$banners[] = $picture_root_url . $banner->file;
		}
		$this->_response['status']                          = 1;
		$this->_response['data']['categories']              = $categories;
		$this->_response['data']['sale_packages']           = $sale_packages;
		$this->_response['data']['merchants']               = $merchants;
		$this->_response['data']['banners']                 = $banners;
		$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}