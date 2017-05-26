<?php

namespace Application\Api\V3\Controllers;

use Phalcon\Db;

class ProductsController extends ControllerBase {
	function indexAction() {
		$merchant_id  = $this->dispatcher->getParam('merchant_id', 'int');
		$category_id  = $this->dispatcher->getParam('category_id', 'int');
		$page         = $this->dispatcher->getParam('page', 'int');
		$search_query = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit        = 10;
		$params       = [];
		$products     = [];
		$merchant_ids = [];
		$merchants    = [];
		$query        = <<<QUERY
			SELECT
				COUNT(DISTINCT d.id)
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
				JOIN products d ON a.id = d.user_id
				JOIN product_categories e ON d.product_category_id = e.id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
				d.published = 1 AND
				e.published = 1 AND
				a.premium_merchant
QUERY;
		if ($this->_premium_merchant) {
			$query .= " = 1 AND a.id = {$this->_premium_merchant->id}";
		} else {
			$query .= ' IS NULL';
			if ($merchant_id) {
				$query .= " AND a.id = {$merchant_id}";
			}
			if ($search_query) {
				$stop_words            = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
				$keywords              = preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY);
				$filtered_keywords     = array_diff($keywords, $stop_words);
				$filtered_search_query = implode(' ', $filtered_keywords);
				$query                .= ' AND (a.company ILIKE ? OR d.name ILIKE ? OR e.name ILIKE ?';
				foreach (range(1, 3) as $i) {
					$params[] = "%{$filtered_search_query}%";
				}
				if (count($filtered_keywords) > 1) {
					foreach ($filtered_keywords as $keyword) {
						$query .= ' OR a.company ILIKE ? OR d.name ILIKE ? OR e.name ILIKE ?';
						foreach (range(1, 3) as $i) {
							$params[] = "%{$keyword}%";
						}
					}
				}
				$query .= ')';
			}
		}
		if ($category_id) {
			$query .= " AND e.id = {$category_id}";
		}
		$total_products   = $this->db->fetchColumn($query, $params);
		$total_pages      = ceil($total_products / $limit);
		$current_page     = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset           = ($current_page - 1) * $limit;
		$result           = $this->db->query(str_replace('COUNT(DISTINCT d.id)', 'd.id, d.user_id AS merchant_id, d.name, d.price, d.stock, d.stock_unit, d.picture', $query) . " GROUP BY d.id ORDER BY d.name LIMIT {$limit} OFFSET {$offset}", $params);
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
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
		if (!$total_products) {
			$this->_response['message'] = $keyword ? 'Produk tidak ditemukan.' : 'Produk belum ada.';
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data'] = [
			'products'     => $products,
			'merchants'    => $merchants,
			'current_hour' => $this->currentDatetime->format('G'),
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}