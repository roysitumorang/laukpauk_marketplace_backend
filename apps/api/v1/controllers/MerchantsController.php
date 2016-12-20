<?php

namespace Application\Api\V1\Controllers;

use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function indexAction() {
		$merchants = [];
		foreach ($this->db->fetchAll("SELECT a.id, a.company, business_days, business_opening_hour, business_closing_hour FROM users a JOIN service_areas b ON a.id = b.user_id WHERE b.village_id = {$this->_current_user->village_id}", Db::FETCH_OBJ) as $merchant) {
			$categories = [];
			foreach ($this->db->fetchAll("SELECT c.id, c.name FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} GROUP BY c.id", Db::FETCH_OBJ) as $category) {
				$products = [];
				foreach ($this->db->fetchAll("SELECT a.id, b.name, a.value, b.unit_size, b.stock_unit, order_closing_hour FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} AND a.published = 1 AND b.published = 1 AND c.id = {$category->id} GROUP BY b.id", Db::FETCH_OBJ) as $product) {
					$products[$product->id] = [
						'name'               => $product->name,
						'price'              => $product->value,
						'unit_size'          => $product->unit_size,
						'stock_unit'         => $product->stock_unit,
						'order_closing_hour' => $product->order_closing_hour,
					];
				}
				$categories[$category->id] = [
					'name'     => $category->name,
					'products' => $products,
				];
			}
			$merchants[$merchant->id] = [
				'company'               => $merchant->company,
				'business_days'         => json_decode($merchant->business_days),
				'business_opening_hour' => $merchant->business_opening_hour,
				'business_closing_hour' => $merchant->business_closing_hour,
				'categories'            => $categories,
			];
		}
		$this->_response['status'] = 1;
		if (!$merchants) {
			$this->_response['message'] = 'Maaf, daerah Anda di luar wilayah operasional Kami.';
		}
		$this->_response['data']['merchants'] = $merchants;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}