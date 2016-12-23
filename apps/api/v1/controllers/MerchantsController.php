<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Setting;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function indexAction() {
		$merchants = [];
		$coupons   = [];
		foreach ($this->db->fetchAll("SELECT a.id, a.company, business_days, business_opening_hour, business_closing_hour FROM users a JOIN service_areas b ON a.id = b.user_id WHERE b.village_id = {$this->_current_user->village_id}", Db::FETCH_OBJ) as $merchant) {
			$categories = [];
			foreach ($this->db->fetchAll("SELECT c.id, c.name FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} AND a.published = 1 AND b.published = 1 AND c.published = 1 GROUP BY c.id", Db::FETCH_OBJ) as $category) {
				$products = [];
				foreach ($this->db->fetchAll("SELECT a.id, b.name, a.value, b.stock_unit, order_closing_hour FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} AND a.published = 1 AND b.published = 1 AND c.published = 1 AND c.id = {$category->id} GROUP BY b.id", Db::FETCH_OBJ) as $product) {
					$products[$product->id] = [
						'name'               => $product->name,
						'price'              => $product->value,
						'stock_unit'         => $product->stock_unit,
					];
					if ($product->order_closing_hour) {
						$products[$product->id]['order_closing_hour'] = $product->order_closing_hour;
					}
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
		$minimum_purchase = Setting::findFirstByName('minimum_purchase')->value;
		$this->_response['data']['merchants']             = $merchants;
		$this->_response['data']['minimum_purchase']      = $minimum_purchase;
		$this->_response['data']['minimum_purchase_html'] = number_format($minimum_purchase);
		$this->_response['data']['coupons']               = $coupons;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}