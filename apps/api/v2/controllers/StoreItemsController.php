<?php

namespace Application\Api\V2\Controllers;

use Application\Models\StoreItem;
use Phalcon\Db;

class StoreItemsController extends ControllerBase {
	function indexAction() {
		$products = [];
		$limit    = 10;
		$keyword  = $this->dispatcher->getParam('keyword', 'string');
		$query    = "SELECT COUNT(1) FROM product_categories a JOIN products b ON a.id = b.product_category_id JOIN store_items c ON b.id = c.product_id AND c.user_id = {$this->_current_user->id} WHERE a.published = 1 AND b.published";
		if ($keyword) {
			$query .= " AND b.name LIKE '%{$keyword}%'";
		}
		$total_products = $this->db->fetchColumn($query);
		$total_pages    = ceil($total_products / $limit);
		$page           = $this->dispatcher->getParam('page', 'int');
		$current_page   = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(str_replace('COUNT(1)', 'b.id, a.name AS category, b.name, b.stock_unit, c.price, c.stock, c.published', $query) . " ORDER BY b.name LIMIT {$limit} OFFSET {$offset}");
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($product = $result->fetch()) {
			$products[] = $product;
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
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function saveAction() {
		$store_items = StoreItem::find(['user_id = ?0', 'bind' => [$this->_current_user->id]]);
		$product_ids = array_keys(get_object_vars($this->_input));
		foreach ($store_items as $store_item) {
			if (!in_array($store_item->product_id, $product_ids)) {
				continue;
			}
			$attributes = $this->_input->{"{$store_item->product_id}"};
			$store_item->setPrice($attributes->price);
			$store_item->setStock($attributes->stock);
			$store_item->setPublished($attributes->published);
			$store_item->updated_by = $this->_current_user->id;
			$store_item->update();
		}
		$this->_response['status']  = 1;
		$this->_response['message'] = 'Update produk berhasil!';
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}