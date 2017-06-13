<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Product;
use DateTimeImmutable;
use Phalcon\Db;

class PricesController extends ControllerBase {
	function indexAction() {
		$products            = [];
		$order_closing_hours = [];
		$limit               = 10;
		$keyword             = $this->dispatcher->getParam('keyword', 'string');
		$query               = "SELECT COUNT(1) FROM product_categories a JOIN products b ON a.id = b.product_category_id WHERE b.user_id = {$this->_current_user->id}";
		if ($keyword) {
			$query .= " AND b.name ILIKE '%{$keyword}%'";
		}
		$total_products = $this->db->fetchColumn($query);
		$total_pages    = ceil($total_products / $limit);
		$page           = $this->dispatcher->getParam('page', 'int');
		$current_page   = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(str_replace('COUNT(1)', 'b.id, a.name AS category, b.name, b.stock_unit, b.price, b.stock, b.published', $query) . " ORDER BY b.name || b.stock_unit LIMIT {$limit} OFFSET {$offset}");
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($product = $result->fetch()) {
			$products[] = $product;
		}
		foreach (range(6, 18) as $i) {
			$order_closing_hours[] = ($i < 10 ? '0' . $i : $i). ':00';
		}
		if (!$total_products) {
			$this->_response['message'] = $keyword ? 'Produk tidak ditemukan.' : 'Produk belum ada.';
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data'] = [
			'products'            => $products,
			'order_closing_hours' => $order_closing_hours,
			'total_pages'         => $total_pages,
			'current_page'        => $current_page,
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function saveAction() {
		$product_ids = array_keys(get_object_vars($this->_input));
		if ($product_ids) {
			$products = Product::find(['user_id = ?0 AND id IN({product_ids:array})', 'bind' => [$this->_current_user->id, 'product_ids' => $product_ids]]);
			foreach ($products as $product) {
				$attributes = $this->_input->{"{$product->id}"};
				if ($product->price != $attributes->price || $product->stock != $attributes->stock || $product->published != $attributes->published) {
					$product->setPrice($attributes->price);
					$product->setStock($attributes->stock);
					$product->setPublished($attributes->published);
					$product->updated_by = $this->_current_user->id;
					$product->update();
				}
			}
		}
		$this->_response = [
			'status'  => 1,
			'message' => 'Update produk berhasil!',
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}