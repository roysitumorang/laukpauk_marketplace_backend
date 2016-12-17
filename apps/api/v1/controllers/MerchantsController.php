<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Role;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function indexAction() {
		$merchants     = [];
		$all_merchants = $this->db->query(sprintf('SELECT a.id, a.company FROM users a JOIN service_areas b ON a.id = b.user_id WHERE a.role_id = %d AND b.village_id = %d', Role::MERCHANT, $this->_access_token->user->village_id));
		$all_merchants->setFetchMode(Db::FETCH_OBJ);
		while ($merchant = $all_merchants->fetch()) {
			$categories     = [];
			$all_categories = $this->db->query("SELECT c.id, c.name FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} GROUP BY c.id");
			$all_categories->setFetchMode(Db::FETCH_OBJ);
			while ($category = $all_categories->fetch()) {
				$products     = [];
				$all_products = $this->db->query("SELECT b.id, b.name, b.stock_unit FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} AND a.published = 1 AND b.published = 1 AND c.id = {$category->id} GROUP BY b.id");
				$all_products->setFetchMode(Db::FETCH_OBJ);
				while ($product = $all_products->fetch()) {
					$prices     = [];
					$all_prices = $this->db->query("SELECT a.id, a.value, a.unit_size, a.order_closing_hour FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} AND a.published = 1 AND b.published = 1 AND c.id = {$category->id}");
					$all_prices->setFetchMode(Db::FETCH_OBJ);
					while ($price = $all_prices->fetch()) {
						$prices[$price->id] = [
							'value'              => $price->value,
							'unit_size'          => $price->unit_size,
							'order_closing_hour' => $price->order_closing_hour,
						];
					}
					$products[$product->id] = [
						'name'       => $product->name,
						'stock_unit' => $product->stock_unit,
						'prices'     => $prices,
					];
				}
				$categories[$category->id] = [
					'name'     => $category->name,
					'products' => $products,
				];
			}
			$merchants[$merchant->id] = [
				'company'    => $merchant->company,
				'categories' => $categories,
			];
		}
		$this->_response['status'] = 1;
		if (!count($merchants)) {
			$this->_response['message'] = 'Maaf, daerah Anda di luar wilayah operasional Kami.';
		}
		$this->_response['data']['merchants'] = $merchants;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}