<?php

namespace Application\Api\V2\Controllers;

use Application\Models\Setting;
use Exception;
use Phalcon\Db;

class ProductsController extends ControllerBase {
	function indexAction() {
		$merchant_id = $this->dispatcher->getParam('merchant_id');
		try {
			if (!filter_var($merchant_id, FILTER_VALIDATE_INT)) {
				throw new Exception('Merchant tidak valid!');
			}
			$query = <<<QUERY
				SELECT
					a.id,
					COALESCE(c.minimum_purchase, a.minimum_purchase) AS minimum_purchase
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN service_areas c ON a.id = c.user_id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
QUERY;
			if ($this->_premium_merchant) {
				$query .= <<<QUERY
					a.premium_merchant = 1 AND
					a.id = {$this->_premium_merchant->id}
QUERY;
			} else {
				$query .= <<<QUERY
					a.premium_merchant IS NULL AND
					a.id = {$merchant_id}
QUERY;
			}
			$merchant = $this->db->fetchOne($query, Db::FETCH_OBJ);
			if (!$merchant) {
				throw new Exception('Merchant tidak valid!');
			}
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
			return $this->response;
		}
		$category_id = $this->dispatcher->getParam('category_id', 'int');
		$page        = $this->dispatcher->getParam('page', 'int');
		$keyword     = $this->dispatcher->getParam('keyword', 'string');
		$limit       = 10;
		$products    = [];
		$query       = <<<QUERY
			SELECT
				COUNT(1)
			FROM
				store_items a
				JOIN products b ON a.product_id = b.id
				JOIN product_categories c ON b.product_category_id = c.id
			WHERE
				a.published = 1 AND
				b.published = 1 AND
				c.published = 1 AND
QUERY;
		if ($this->_premium_merchant) {
			$query .= " a.user_id = {$this->_premium_merchant->id}";
		} else {
			$query .= " a.user_id = {$merchant->id}";
		}
		if ($category_id && $category = $this->db->fetchOne("SELECT id FROM product_categories WHERE id = {$category_id}", Db::FETCH_OBJ)) {
			$query .= " AND c.id = {$category->id}";
		}
		if ($keyword) {
			$query .= " AND b.name LIKE '%{$keyword}%'";
		}
		$total_products = $this->db->fetchColumn($query);
		$total_pages    = ceil($total_products / $limit);
		$current_page   = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(str_replace('COUNT(1)', 'a.id, b.product_category_id, b.name, a.price, a.stock, b.stock_unit, order_closing_hour', $query) . " GROUP BY b.id ORDER BY b.name LIMIT {$limit} OFFSET {$offset}");
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$product = [
				'id'         => $row->id,
				'name'       => $row->name,
				'price'      => $row->price,
				'stock'      => $row->stock,
				'stock_unit' => $row->stock_unit,
			];
			if ($row->order_closing_hour) {
				$product['order_closing_hour'] = $row->order_closing_hour;
			}
			$products[] = $product;
		}
		if (!$total_products) {
			$this->_response['message'] = $keyword ? 'Produk tidak ditemukan.' : 'Produk belum ada.';
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data'] = [
			'products'         => $products,
			'total_pages'      => $total_pages,
			'current_page'     => $current_page,
			'current_hour'     => $this->currentDatetime->format('G'),
			'minimum_purchase' => $merchant->minimum_purchase ?: Setting::findFirstByName('minimum_purchase')->value,
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}