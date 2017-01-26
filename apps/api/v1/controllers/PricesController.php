<?php

namespace Application\Api\V1\Controllers;

use DateTimeImmutable;
use Phalcon\Db;

class PricesController extends ControllerBase {
	function indexAction() {
		$products = [];
		$limit    = 10;
		$keyword  = $this->dispatcher->getParam('keyword', 'string');
		$query    = "SELECT COUNT(1) FROM product_categories a JOIN products b ON a.id = b.product_category_id LEFT JOIN product_prices c ON b.id = c.product_id AND c.user_id = {$this->_current_user->id} WHERE a.published = 1";
		if ($keyword) {
			$query .= " AND b.name LIKE '%{$keyword}%'";
		}
		$total_products = $this->db->fetchColumn($query);
		$total_pages    = ceil($total_products / $limit);
		$page           = $this->dispatcher->getParam('page', 'int');
		$current_page   = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(str_replace('COUNT(1)', 'b.id, a.name AS category, b.name, b.stock_unit, COALESCE(c.value, 0) AS price, COALESCE(c.published, 0) AS published, c.order_closing_hour', $query) . " ORDER BY b.name LIMIT {$limit} OFFSET {$offset}");
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
		foreach ($this->_input as $product_id => $attributes) {
			$price              = filter_var($attributes->price, FILTER_VALIDATE_INT) ?: 0;
			$published          = in_array($attributes->published, [0, 1]) ? $attributes->published : 0;
			$current_datetime   = $this->currentDatetime->format('Y-m-d H:i:s');
			$order_closing_hour = DateTimeImmutable::createFromFormat('H:i', $attributes->order_closing_hour) ? $attributes->order_closing_hour : null;
			$this->db->execute('INSERT INTO product_prices (user_id, product_id, value, published, order_closing_hour, created_by, created_at) VALUES (:current_user_id, :product_id, :price, :published, :order_closing_hour, :current_user_id, :current_datetime) ON DUPLICATE KEY UPDATE value = :price, published = :published, order_closing_hour = :order_closing_hour, updated_by = :current_user_id, updated_at = :current_datetime', [
				'current_user_id'    => $this->_current_user->id,
				'product_id'         => $product_id,
				'price'              => $price,
				'published'          => $published,
				'order_closing_hour' => $order_closing_hour,
				'current_datetime'   => $current_datetime,
			]);
		}
		$this->_response = [
			'status'  => 1,
			'message' => 'Update produk berhasil!',
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}