<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Role;
use Application\Models\User;
use Exception;
use Phalcon\Crypt;
use Phalcon\Mvc\Controller;

abstract class ControllerBase extends Controller {
	protected $_response = [
		'status'          => -1,
		'invalid_api_key' => 0,
		'message'         => null,
		'data'            => [],
	];
	protected $_current_user;
	protected $_input;

	function initialize() {
		$this->_input = $this->request->getJsonRawBody();
	}

	function beforeExecuteRoute() {
		try {
			$access_token = $this->request->get('access_token', 'string');
			if (!$access_token) {
				throw new Exception;
			}
			$encrypted_data      = strtr($access_token, ['-' => '+', '_' => '/', ',' => '=']);
			$crypt               = new Crypt;
			$api_key             = $crypt->decryptBase64($encrypted_data, $this->config->encryption_key);
			$this->_current_user = User::findFirst(['status = 1 AND role_id > 2 AND api_key = ?0', 'bind' => [
				$api_key,
			]]);
			if (!$this->_current_user) {
				throw new Exception;
			}
		} catch (Exception $e) {
			$this->_response['message']         = 'API key tidak valid!';
			$this->_response['invalid_api_key'] = 1;
			$this->response->setJsonContent($this->_response);
			exit($this->response->send());
		}
	}
}
