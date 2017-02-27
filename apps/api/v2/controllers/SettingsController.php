<?php

namespace Application\Api\V2\Controllers;

use Application\Models\ProductCategory;
use Phalcon\Db;

class SettingsController extends ControllerBase {
	function indexAction() {
		$this->_response['status']                     = 1;
		$this->_response['data']['product_categories'] = [];
		$this->_response['data']['products']           = [];
		$this->_response['data']['subdistricts']       = [];
		$this->_response['data']['villages']           = [];
		$product_categories                            = ProductCategory::find([
			'conditions' => 'published = 1',
			'order'      => 'name',
		]);
		foreach ($product_categories as $product_category) {
			$this->_response['data']['product_categories'][$product_category->id] = $product_category->name;
			$products = $product_category->getProducts([
				'conditions' => "published = 1 AND product_category_id = {$product_category->id}",
				'columns'    => 'id, name',
				'order'      => 'name',
			]);
			foreach ($products as $product) {
				$this->_response['data']['products'][$product_category->id][$product->id] = $product->name;
			}
		}
		$subdistricts = [];
		$result       = $this->db->query("SELECT a.id, a.name FROM subdistricts a JOIN cities b ON a.city_id = b.id WHERE b.name = 'Medan' ORDER BY a.name");
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($subdistrict = $result->fetch()) {
			$subdistricts[$subdistrict->id] = $subdistrict->name;
		}
		$villages = [];
		$result   = $this->db->query('SELECT id, subdistrict_id, name FROM villages ORDER BY subdistrict_id, name');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			isset($villages[$item->subdistrict_id]) || $villages[$item->subdistrict_id] = [];
			$villages[$item->subdistrict_id][$item->id] = $item->name;
		}
		$this->_response['data']['subdistricts'] = $subdistricts;
		$this->_response['data']['villages']     = $villages;
		if ($this->_current_user) {
			$this->_response['data']['merchants'] = [];
			$result = $this->db->query("SELECT a.id, a.name FROM users a JOIN service_areas b ON a.id = b.user_id ORDER BY a.name WHERE b.village_id = {$this->_current_user->village_id}");
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($merchant = $result->fetch()) {
				$this->response['data']['merchants'][$merchant->id] = $merchant->name;
			}
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
