<?php

namespace Application\Api\Controllers;

use Application\Model\AccessToken;
use Exception;
use Phalcon\Mvc\Controller;

abstract class BaseController extends Controller {
	protected $_response = [
		'status'  => -1,
		'message' => null,
		'data'    => [],
	];
	protected $_current_user;

	function initialize() {
		try {
			$access_token = AccessToken::findFirst($this->request->getServer('Authorization'));
			if (!$access_token || $access_token->ip_address != $this->request->getClientAddress() || $access_token->user_agent != $this->request->getUserAgent()) {
				throw new Exception('Token tidak valid!');
			}
			if ($access_token->expired_at >= time()) {
				throw new Exception('Token expired!');
			}
			$access_token->update(['updated_at' => time()]);
			$this->_current_user = $access_token->user;
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}