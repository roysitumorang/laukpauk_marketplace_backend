<?php

namespace Application\Api\V2\Controllers;

use Application\Models\Product;
use Phalcon\Db;

class StoreItemsController extends ControllerBase {
	function indexAction() {
		$products = [];
		$limit    = 10;
		$keyword  = $this->dispatcher->getParam('keyword', 'string');
		$query    = "SELECT COUNT(1) FROM product_categories a JOIN products b ON a.id = b.product_category_id WHERE b.user_id = {$this->_current_user->id} AND a.published = 1 AND b.published";
		if ($keyword) {
			$query .= " AND b.name LIKE '%{$keyword}%'";
		}
		$total_products = $this->db->fetchColumn($query);
		$total_pages    = ceil($total_products / $limit);
		$page           = $this->dispatcher->getParam('page', 'int');
		$current_page   = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(str_replace('COUNT(1)', 'b.id, a.name AS category, b.name, b.stock_unit, b.price, b.stock, b.published', $query) . " ORDER BY b.name LIMIT {$limit} OFFSET {$offset}");
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
		$products    = Product::findByUserId($this->_current_user->id);
		$product_ids = array_keys(get_object_vars($this->_input));
		foreach ($products as $product) {
			if (!in_array($product->id, $product_ids)) {
				continue;
			}
			$attributes = $this->_input->{"{$product->id}"};
			$product->setPrice($attributes->price);
			$product->setStock($attributes->stock);
			$product->setPublished($attributes->published);
			$product->updated_by = $this->_current_user->id;
			$product->update();
		}
		$this->_response['status']  = 1;
		$this->_response['message'] = 'Update produk berhasil!';
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}