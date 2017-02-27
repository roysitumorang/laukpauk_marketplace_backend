<?php

namespace Application\Api\V2\Controllers;

use Application\Models\Setting;
use Application\Models\User;
use Exception;
use Phalcon\Crypt;
use Phalcon\Mvc\Controller;

abstract class ControllerBase extends Controller {
	protected $_response = [
		'status' => -1,
		'data'   => [],
	];
	protected $_current_user;
	protected $_input;

	function initialize() {
		if (Setting::findFirstByName('maintenance_mode')->value) {
			$this->_response['maintenance_mode'] = 1;
			$this->response->setJsonContent($this->_response);
			exit($this->response->send());
		}
		$this->_input = $this->request->getJsonRawBody();
	}

	function beforeExecuteRoute() {
		try {
			$access_token = $this->request->get('access_token', 'string');
			if (!$access_token) {
				$this->_response['invalid_api_key'] = 1;
				throw new Exception('API key tidak valid!');
			}
			$encrypted_data      = strtr($access_token, ['-' => '+', '_' => '/', ',' => '=']);
			$crypt               = new Crypt;
			$api_key             = $crypt->decryptBase64($encrypted_data, $this->config->encryption_key);
			$this->_current_user = User::findFirst(['status = 1 AND role_id > 2 AND api_key = ?0', 'bind' => [
				$api_key,
			]]);
			if (!$this->_current_user) {
				$this->_response['invalid_api_key'] = 1;
				throw new Exception('API key tidak valid!');
			}
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response);
			exit($this->response->send());
		}
	}
}
