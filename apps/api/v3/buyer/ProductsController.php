<?php

namespace Application\Api\V3\Buyer;

use Ds\Set;
use Phalcon\Db\Enum;

class ProductsController extends ControllerBase {
	function indexAction() {
		$merchant_id  = $this->dispatcher->getParam('merchant_id', 'int!');
		$category_id  = $this->dispatcher->getParam('category_id', 'int!');
		$current_page = $this->dispatcher->getParam('page', 'int!', 1);
		$search_query = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit        = 10;
		$params       = [];
		$products     = [];
		$merchant_ids = new Set;
		$merchants    = [];
		$stop_words   = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
		$keywords     = '';
		if ($search_query) {
			$words = array_values(array_diff(preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY), $stop_words));
			foreach ($words as $i => $word) {
				$keywords .= ($i > 0 ? ' & ' : '') . $word . ':*';
			}
		}
		$query = <<<QUERY
			SELECT
				COUNT(DISTINCT d.id)
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN coverage_area c ON a.id = c.user_id
				JOIN user_product d ON a.id = d.user_id
				JOIN products e ON d.product_id = e.id
				LEFT JOIN product_group_member g ON e.id = g.product_id
				LEFT JOIN product_groups h ON g.product_group_id = h.id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
				d.published = 1 AND
				e.published = 1
QUERY;
		if ($merchant_id) {
			$query .= " AND a.id = {$merchant_id}";
		}
		if ($search_query) {
			if ($this->db->fetchColumn('SELECT COUNT(1) FROM product_groups WHERE published = 1 AND name = ?', [$search_query])) {
				$query   .= ' AND h.published = 1 AND h.name = ?';
				$params[] = $search_query;
			} else if ($keywords) {
				$query .= ' AND (';
				foreach (['e.keywords', 'a.keywords'] as $i => $field) {
					$query .= ($i ? ' OR ' : '') . "{$field} @@ TO_TSQUERY('{$keywords}')";
				}
				$query .= ')';
			}
		}
		if ($category_id) {
			$query .= " AND e.product_category_id = {$category_id}";
		}
		$total_products = $this->db->fetchColumn($query, $params);
		$total_pages    = ceil($total_products / $limit);
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(strtr($query, ['COUNT(DISTINCT d.id)' => <<<QUERY
			DISTINCT
			d.id,
			e.name,
			d.price,
			d.stock,
			e.stock_unit,
			e.picture,
			d.user_id,
			TS_RANK(e.keywords, TO_TSQUERY('{$keywords}')) + TS_RANK(a.keywords, TO_TSQUERY('{$keywords}')) AS relevancy
QUERY
		]) . " ORDER BY relevancy DESC, e.name LIMIT {$limit} OFFSET {$offset}", $params);
		$picture_root_url = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result->setFetchMode(Enum::FETCH_OBJ);
		while ($row = $result->fetch()) {
			unset($row->relevancy);
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
			$result->setFetchMode(Enum::FETCH_OBJ);
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
		if (!$total_products) {
			if ($keywords) {
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