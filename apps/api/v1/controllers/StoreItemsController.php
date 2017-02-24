<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Product;
use Application\Models\StoreItem;
use DateTimeImmutable;
use Phalcon\Db;

class StoreItemsController extends ControllerBase {
	function indexAction() {
		$products            = [];
		$order_closing_hours = [];
		$limit               = 10;
		$keyword             = $this->dispatcher->getParam('keyword', 'string');
		$query               = "SELECT COUNT(1) FROM product_categories a JOIN products b ON a.id = b.product_category_id LEFT JOIN store_items c ON b.id = c.product_id AND c.user_id = {$this->_current_user->id} WHERE a.published = 1";
		if ($keyword) {
			$query .= " AND b.name LIKE '%{$keyword}%'";
		}
		$total_products = $this->db->fetchColumn($query);
		$total_pages    = ceil($total_products / $limit);
		$page           = $this->dispatcher->getParam('page', 'int');
		$current_page   = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(str_replace('COUNT(1)', 'b.id, a.name AS category, b.name, b.stock_unit, c.price, c.stock, c.published, c.order_closing_hour', $query) . " ORDER BY b.name LIMIT {$limit} OFFSET {$offset}");
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
		foreach ($this->_input as $product_id => $attributes) {
			$product = Product::findFirstById($product_id);
			if (!$product) {
				continue;
			}
			$store_item = StoreItem::findFirst(['user_id = ?0 AND product_id = ?1', 'bind' => [$this->_current_user->id, $product_id]]);
			if (!$store_item) {
				$store_item             = new StoreItem;
				$store_item->user       = $this->_current_user;
				$store_item->product    = $product;
				$store_item->created_by = $this->_current_user->id;
			} else {
				$store_item->updated_by = $this->_current_user->id;
			}
			$store_item->setPrice($attributes->price);
			$store_item->setStock($attributes->stock);
			$store_item->setPublished($attributes->published);
			$store_item->setOrderClosingHour(DateTimeImmutable::createFromFormat('H:i', $attributes->order_closing_hour) ? $attributes->order_closing_hour : null);
			$store_item->save();
		}
		$this->_response = [
			'status'  => 1,
			'message' => 'Update produk berhasil!',
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}