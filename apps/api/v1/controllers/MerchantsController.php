<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Setting;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function indexAction() {
		$current_day = $this->currentDatetime->format('l');
		$merchants   = [];
		$coupons     = [];

		foreach ($this->db->fetchAll("SELECT a.id, a.company, open_on_sunday, open_on_monday, open_on_tuesday, open_on_wednesday, open_on_thursday, open_on_friday, open_on_saturday, business_opening_hour, business_closing_hour FROM users a JOIN service_areas b ON a.id = b.user_id WHERE b.village_id = {$this->_current_user->village_id}", Db::FETCH_OBJ) as $merchant) {
			$categories    = [];
			foreach ($this->db->fetchAll("SELECT c.id, c.name FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} AND a.published = 1 AND a.value > 0 AND b.published = 1 AND c.published = 1 GROUP BY c.id", Db::FETCH_OBJ) as $category) {
				$products = [];
				foreach ($this->db->fetchAll("SELECT a.id, b.name, a.value, b.stock_unit, order_closing_hour FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$merchant->id} AND a.published = 1 AND a.value > 0 AND b.published = 1 AND c.published = 1 AND c.id = {$category->id} GROUP BY b.id", Db::FETCH_OBJ) as $product) {
					$products[$product->id] = [
						'name'       => $product->name,
						'price'      => $product->value,
						'stock_unit' => $product->stock_unit,
					];
					if ($product->order_closing_hour) {
						$products[$product->id]['order_closing_hour'] = $product->order_closing_hour;
					}
				}
				if ($products) {
					$categories[$category->id] = [
						'name'     => $category->name,
						'products' => $products,
					];
				}
			}
			if (!$categories) {
				continue;
			}
			$merchants[$merchant->id] = [
				'company'               => $merchant->company,
				'open_on_sunday'        => $merchant->open_on_sunday,
				'open_on_monday'        => $merchant->open_on_monday,
				'open_on_tuesday'       => $merchant->open_on_tuesday,
				'open_on_wednesday'     => $merchant->open_on_wednesday,
				'open_on_thursday'      => $merchant->open_on_thursday,
				'open_on_friday'        => $merchant->open_on_friday,
				'open_on_saturday'      => $merchant->open_on_saturday,
				'business_opening_hour' => $merchant->business_opening_hour,
				'business_closing_hour' => $merchant->business_closing_hour,
				'categories'            => $categories,
			];
			if ($current_day == 'Sunday' && !$merchant->open_on_sunday ||
				$current_day == 'Monday' && !$merchant->open_on_monday ||
				$current_day == 'Tuesday' && !$merchant->open_on_tuesday ||
				$current_day == 'Wednesday' && !$merchant->open_on_wednesday ||
				$current_day == 'Thursday' && !$merchant->open_on_thursday ||
				$current_day == 'Friday' && !$merchant->open_on_friday ||
				$current_day == 'Saturday' && !$merchant->open_on_saturday) {
				$merchants[$merchant->id]['unavailable'] = 1;
			}
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