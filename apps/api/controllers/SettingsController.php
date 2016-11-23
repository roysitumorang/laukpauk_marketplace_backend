<?php

namespace Application\Api\Controllers;

use Application\Models\ProductCategory;
use Application\Models\Subdistrict;

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
		$subdistricts                                  = Subdistrict::find([
			'conditions' => "name LIKE 'Medan%'",
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
		foreach ($subdistricts as $subdistrict) {
			$villages = $subdistrict->getVillages([
				'columns' => 'id, name, zip_code',
				'order'   => 'name',
			])->toArray();
			if (!$villages) {
				continue;
			}
			$this->_response['data']['villages'][$subdistrict->id] = $villages;
			$this->_response['data']['subdistricts'][]             = [
				'id'   => $subdistrict->id,
				'name' => $subdistrict->name,
			];
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
