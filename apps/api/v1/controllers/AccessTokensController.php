<?php

namespace Application\Api\V1\Controllers;

use Application\Models\AccessToken;

class AccessTokensController extends ControllerBase {
	function initialize() {}

	function beforeExecuteRoute() {}

	function createAction() {
		$access_token = new AccessToken;
		$access_token->create();
		$this->_response['status']               = 1;
		$this->_response['data']['access_token'] = $access_token->id;
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}
}
