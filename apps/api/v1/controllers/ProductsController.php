<?php

namespace Application\Api\V1\Controllers;

use Application\Models\ProductCategory;

class ProductsController extends BaseController {
	function indexAction() {
		$product_category_id = $this->request->getQuery('product_category_id', 'int');
		if (!$product_category_id || !($product_category = ProductCategory::findFirst("published = 1 AND id = {$product_category_id}"))) {
			$this->response->setStatusCode(404, 'Not Found');
			$this->_response['message'] = 'Produk tidak ditemukan';
		} else {
			$this->_response['status'] = 1;
			$this->_response['data']   = $product_category->getProducts([
				'conditions' => 'published = 1',
				'columns'    => 'id, name, description',
				'order'      => 'name',
			])->toArray();
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}