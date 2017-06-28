<?php

namespace Application\Api\V3\Buyer;

use Ds\Set;
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
		$merchant_ids = new Set;
		$merchants    = [];
		$stop_words   = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
		$query        = <<<QUERY
			SELECT
				COUNT(DISTINCT d.id)
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN coverage_area c ON a.id = c.user_id
				JOIN user_product d ON a.id = d.user_id
				JOIN products e ON d.product_id = e.id
				JOIN product_categories f ON e.product_category_id = f.id
				LEFT JOIN product_group_member g ON e.id = g.product_id
				LEFT JOIN product_groups h ON g.product_group_id = h.id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
				d.published = 1 AND
				e.published = 1 AND
				f.published = 1 AND
				a.premium_merchant
QUERY;
		if ($this->_premium_merchant) {
			$query .= " = 1 AND a.id = {$this->_premium_merchant->id}";
			if ($search_query) {
				$keywords              = preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY);
				$filtered_keywords     = array_diff($keywords, $stop_words);
				$filtered_search_query = implode(' ', $filtered_keywords);
				$query                .= ' AND (e.name ILIKE ? OR f.name ILIKE ?';
				foreach (range(1, 2) as $i) {
					$params[] = "%{$filtered_search_query}%";
				}
				if (count($filtered_keywords) > 1) {
					foreach ($filtered_keywords as $keyword) {
						$query .= ' OR e.name ILIKE ? OR f.name ILIKE ?';
						foreach (range(1, 2) as $i) {
							$params[] = "%{$keyword}%";
						}
					}
				}
				$query .= ')';
			}
		} else {
			$query .= ' IS NULL';
			if ($merchant_id) {
				$query .= " AND a.id = {$merchant_id}";
			}
			if ($search_query) {
				if ($this->db->fetchColumn('SELECT COUNT(1) FROM product_groups WHERE published = 1 AND name = ?', [$search_query])) {
					$query   .= ' AND h.published = 1 AND h.name = ?';
					$params[] = $search_query;
				} else {
					$keywords              = preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY);
					$filtered_keywords     = array_diff($keywords, $stop_words);
					$filtered_search_query = implode(' ', $filtered_keywords);
					$query                .= ' AND (a.company ILIKE ? OR e.name ILIKE ? OR f.name ILIKE ?';
					foreach (range(1, 3) as $i) {
						$params[] = "%{$filtered_search_query}%";
					}
					if (count($filtered_keywords) > 1) {
						foreach ($filtered_keywords as $keyword) {
							$query .= ' OR a.company ILIKE ? OR e.name ILIKE ? OR f.name ILIKE ?';
							foreach (range(1, 3) as $i) {
								$params[] = "%{$keyword}%";
							}
						}
					}
					$query .= ')';
				}
			}
		}
		if ($category_id) {
			$query .= " AND f.id = {$category_id}";
		}
		$total_products   = $this->db->fetchColumn($query, $params);
		$total_pages      = ceil($total_products / $limit);
		$current_page     = $page > 0 ? $page : 1;
		$offset           = ($current_page - 1) * $limit;
		$result           = $this->db->query(strtr($query, ['COUNT(DISTINCT d.id)' => 'DISTINCT d.id, e.name, d.price, d.stock, e.stock_unit, e.picture, d.user_id']) . " ORDER BY e.name LIMIT {$limit} OFFSET {$offset}", $params);
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			if (!$merchant_ids->contains($row->user_id)) {
				$merchant_ids->add($row->user_id);
			}
			if ($row->picture) {
				$row->thumbnail = $picture_root_url . strtr($row->picture, ['.jpg' => '120.jpg']);
				$row->picture   = $picture_root_url . strtr($row->picture, ['.jpg' => '300.jpg']);
			} else {
				unset($row->picture);
			}
			$products[] = $row;
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
				if (($today == 1 && $item->open_on_monday) ||
					($today == 2 && $item->open_on_tuesday) ||
					($today == 3 && $item->open_on_wednesday) ||
					($today == 4 && $item->open_on_thursday) ||
					($today == 5 && $item->open_on_friday) ||
					($today == 6 && $item->open_on_saturday) ||
					($today == 7 && $item->open_on_sunday)) {
					$availability .= 'buka';
				} else {
					$availability .= 'tutup';
				}
				$availability .= ', besok ';
				if (($tomorrow == 1 && $item->open_on_monday) ||
					($tomorrow == 2 && $item->open_on_tuesday) ||
					($tomorrow == 3 && $item->open_on_wednesday) ||
					($tomorrow == 4 && $item->open_on_thursday) ||
					($tomorrow == 5 && $item->open_on_friday) ||
					($tomorrow == 6 && $item->open_on_saturday) ||
					($tomorrow == 7 && $item->open_on_sunday)) {
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
		if (!$total_products) {
			if ($keyword) {
				$this->_response['message'] = 'Produk tidak ditemukan.';
			} else if (!$total_pages) {
				$this->_response['message'] = 'Produk belum ada.';
			}
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data']['products']     = $products;
		$this->_response['data']['current_hour'] = $this->currentDatetime->format('G');
		$this->_response['data']['merchants']    = $merchants;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}