<?php

namespace Application\Api\V3\Buyer;

use Application\Models\User;
use Ds\Map;
use Phalcon\Exception;

class PostsController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($merchant_token = $this->dispatcher->getParam('merchant_token', 'string')) {
			$this->_premium_merchant = User::findFirst(['status = 1 AND role_id = ?0 AND premium_merchant = 1 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);
		}
	}

	function showAction($permalink) {
		try {
			$user       = $this->_premium_merchant ?: User::findFirst(1);
			$permalinks = new Map([
				'hubungi-kami' => 'contact',
			]);
			if (!$permalinks->hasKey($permalink)) {
				throw new Exception('Konten tidak ditemukan!');
			}
			$attribute                       = $permalinks->get($permalink);
			$this->_response['status']       = 1;
			$this->_response['data']['post'] = [
				'subject' => ucwords(strtr($permalink, ['-' => ' '])),
				'body'    => strtr($user->$attribute, ["\r\n" => '']),
			];
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}