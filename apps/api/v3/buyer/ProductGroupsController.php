<?php

namespace Application\Api\V3\Buyer;

use Phalcon\Db;

class ProductGroupsController extends ControllerBase {
	function indexAction() {
		$page           = $this->dispatcher->getParam('page', 'int');
		$search_query   = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit          = 10;
		$product_groups = [];
		$query          = 'SELECT COUNT(1) FROM product_groups WHERE published = 1';
		$keywords       = '';
		if ($search_query) {
			$words = preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($words as $i => $word) {
				$keywords .= ($i > 0 ? ' & ' : '') . $word . ':*';
			}
			if ($keywords) {
				$query .= " AND keywords @@ TO_TSQUERY('{$keywords}')";
			}
		}
		$total_rows   = $this->db->fetchColumn($query);
		$total_pages  = ceil($total_rows / $limit);
		$current_page = $page > 0 ? $page : 1;
		$offset       = ($current_page - 1) * $limit;
		$result       = $this->db->query(strtr($query, ['COUNT(1)' => 'name' . ($keywords ? ", TS_RANK(keywords, TO_TSQUERY('{$keywords}')) AS relevancy" : '')]) . ' ORDER BY ' . ($keywords ? 'relevancy DESC,' : '') . "name LIMIT {$limit} OFFSET {$offset}");
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			unset($row->relevancy);
			$product_groups[] = $row;
		}
		if (!$total_rows) {
			if ($keywords) {
				$this->_response['message'] = 'Resep tidak ditemukan.';
			} else if (!$total_pages) {
				$this->_response['message'] = 'Resep belum ada.';
			}
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data']['product_groups'] = $product_groups;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}