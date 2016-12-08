<?php

namespace Application\Api\V1\Controllers;

use Application\Models\AccessToken;
use Exception;
use Phalcon\Mvc\Controller;

abstract class ControllerBase extends Controller {
	protected $_response = [
		'status'  => -1,
		'message' => null,
		'data'    => [],
	];
	protected $_access_token;
	protected $_input;

	function initialize() {
		$this->_input = $this->request->getJsonRawBody();
	}

	function beforeExecuteRoute() {
		try {
			$this->_access_token = AccessToken::findFirstById($this->request->get('access_token'));
			if (!$this->_access_token || $this->_access_token->ip_address != $this->request->getClientAddress() || $this->_access_token->user_agent != $this->request->getUserAgent()) {
				throw new Exception('Token tidak valid!');
			}
			if ($this->_access_token->expired_at >= time()) {
				throw new Exception('Token expired!');
			}
			$this->_access_token->update(['updated_at' => time()]);
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response);
			exit($this->response->send());
		}
	}
}
