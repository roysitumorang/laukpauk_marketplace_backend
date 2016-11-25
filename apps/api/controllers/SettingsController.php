<?php

namespace Application\Api\Controllers;

use Application\Models\ProductCategory;
use Phalcon\Db;

class SettingsController extends BaseController {
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
			$this->_response['data']['product_categories'][] = [
				'id'          => $product_category->id,
				'name'        => $product_category->name,
				'description' => $product_category->description,
			];
			$this->_response['data']['products'][$product_category->id] = $product_category->getProducts([
				'conditions' => "published = 1 AND product_category_id = {$product_category->id}",
				'columns'    => 'id, name, description',
				'order'      => 'name',
			])->toArray();
		}
		if (!apcu_exists('subdistricts')) {
			$subdistricts = [];
			$result       = $this->db->query("SELECT a.id, a.name FROM subdistricts a JOIN cities b ON a.city_id = b.id WHERE b.name = 'Medan' ORDER BY a.name");
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($subdistrict = $result->fetch()) {
				$subdistricts[] = $subdistrict;
			}
			apcu_add('subdistricts', $subdistricts);
		}
		if (!apcu_exists('villages')) {
			$villages = [];
			$result   = $this->db->query('SELECT id, subdistrict_id, name FROM villages ORDER BY subdistrict_id, name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				isset($villages[$item->subdistrict_id]) || $villages[$item->subdistrict_id] = [];
				$village = clone $item;
				unset($village->subdistrict_id);
				$villages[$item->subdistrict_id][] = $village;
			}
			apcu_add('villages', $villages);
		}
		$this->_response['data']['subdistricts'] = apcu_fetch('subdistricts');
		$this->_response['data']['villages']     = apcu_fetch('villages');
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}