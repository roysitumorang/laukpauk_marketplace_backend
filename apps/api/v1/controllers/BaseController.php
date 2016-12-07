<?php

namespace Application\Api\V1\Controllers;

use Application\Models\AccessToken;
use Exception;
use Phalcon\Mvc\Controller;

abstract class BaseController extends Controller {
	protected $_response = [
		'status'  => -1,
		'message' => null,
		'data'    => [],
	];
	protected $_access_token;

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
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}
