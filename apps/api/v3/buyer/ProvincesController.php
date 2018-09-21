<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Province;

class ProvincesController extends ControllerBase {
	function beforeExecuteRoute() {}

	function indexAction() {
		$this->_response['status'] = 1;
		$this->_response['data']   = [
			'provinces' => Province::find([
				'columns' => 'id, name',
				'order'   => 'name',
			])->toArray()
		];
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}
}
