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
		$query        = <<<QUERY
			SELECT
				COUNT(DISTINCT f.id)
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
				JOIN settings d ON d.name = 'minimum_purchase'
				JOIN store_items e ON a.id = e.user_id
				JOIN products f ON e.product_id = f.id
				JOIN product_categories g ON f.product_category_id = g.id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
				e.published = 1 AND
				f.published = 1 AND
				g.published = 1 AND
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
				$query                .= ' AND (a.company LIKE ? OR f.name LIKE ? OR g.name LIKE ?';
				foreach (range(1, 3) as $i) {
					$params[] = "%{$filtered_search_query}%";
				}
				if (count($filtered_keywords) > 1) {
					foreach ($filtered_keywords as $keyword) {
						$query .= ' OR a.company LIKE ? OR f.name LIKE ? OR g.name LIKE ?';
						foreach (range(1, 3) as $i) {
							$params[] = "%{$keyword}%";
						}
					}
				}
				$query .= ')';
			}
		}
		if ($category_id) {
			$query .= " AND g.id = {$category_id}";
		}
		if ($keyword) {
			$query .= " AND f.name LIKE '%{$keyword}%'";
		}
		$total_products   = $this->db->fetchColumn($query, $params);
		$total_pages      = ceil($total_products / $limit);
		$current_page     = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset           = ($current_page - 1) * $limit;
		$result           = $this->db->query(str_replace('COUNT(DISTINCT f.id)', 'f.id, f.name, e.price, e.stock, f.stock_unit, e.order_closing_hour, f.picture', $query) . " GROUP BY f.id ORDER BY f.name LIMIT {$limit} OFFSET {$offset}", $params);
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
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
		if (!$total_products) {
			$this->_response['message'] = $keyword ? 'Produk tidak ditemukan.' : 'Produk belum ada.';
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data'] = [
			'products'     => $products,
			'total_pages'  => $total_pages,
			'current_page' => $current_page,
			'current_hour' => $this->currentDatetime->format('G'),
			'pages'        => $this->_setPaginationRange($total_pages, $current_page),
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}