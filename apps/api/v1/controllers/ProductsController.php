<?php

namespace Application\Api\V1\Controllers;

use Phalcon\Db;
use Application\Models\Setting;

class ProductsController extends ControllerBase {
	function indexAction() {
		$merchant_id = $this->dispatcher->getParam('merchant_id');
		try {
			if (!filter_var($merchant_id, FILTER_VALIDATE_INT)) {
				throw new Exception('Merchant tidak valid!');
			}
			$merchant = $this->db->fetchOne(<<<QUERY
				SELECT
					a.id,
					a.minimum_purchase
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN service_areas c ON a.id = c.user_id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
					a.id = {$merchant_id}
				ORDER BY a.company
QUERY
			, Db::FETCH_OBJ);
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
		$query       = "SELECT COUNT(1) FROM products b JOIN product_categories c ON b.product_category_id = c.id WHERE b.user_id = {$merchant->id} AND b.published = 1 AND c.published = 1";
		if ($category_id && $category = $this->db->fetchOne("SELECT id FROM product_categories WHERE id = {$category_id} AND user_id IS NULL", Db::FETCH_OBJ)) {
			$query .= " AND b.product_category_id = {$category->id}";
		}
		if ($keyword) {
			$query .= " AND b.name ILIKE '%{$keyword}%'";
		}
		$total_products = $this->db->fetchColumn($query);
		$total_pages    = ceil($total_products / $limit);
		$current_page   = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(str_replace('COUNT(1)', 'b.id, b.product_category_id, b.name, b.price, b.stock, b.stock_unit', $query) . " GROUP BY b.id ORDER BY b.name || b.stock_unit LIMIT {$limit} OFFSET {$offset}");
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$product = [
				'id'         => $row->id,
				'name'       => $row->name,
				'price'      => $row->price,
				'stock'      => $row->stock,
				'stock_unit' => $row->stock_unit,
			];
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