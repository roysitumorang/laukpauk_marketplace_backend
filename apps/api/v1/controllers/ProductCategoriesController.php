<?php

namespace Application\Api\V1\Controllers;

use Application\Models\ProductCategory;

class ProductCategoriesController extends BaseController {
	function indexAction() {
		$this->_response['status'] = 1;
		$this->_response['data']   = ProductCategory::find([
			'conditions' => 'published = 1',
			'columns'    => 'id, name, description',
		])->toArray();
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}
}