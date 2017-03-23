<?php

namespace Application\Api\V2\Controllers;

use Application\Models\Role;
use Application\Models\Setting;
use Application\Models\User;
use Exception;
use Phalcon\Crypt;
use Phalcon\Mvc\Controller;

abstract class ControllerBase extends Controller {
	const INVALID_API_KEY_MESSAGE = 'API key tidak valid, silahkan logout dan login kembali!';
	protected $_response = [
		'status' => -1,
		'data'   => [],
	];
	protected $_current_user;
	protected $_premium_merchant;
	protected $_input;

	function initialize() {
		$this->_response['version'] = $this->db->fetchColumn('SELECT MAX(`version`) FROM releases');
		if (Setting::findFirstByName('maintenance_mode')->value) {
			$this->_response['maintenance_mode'] = 1;
			$this->response->setJsonContent($this->_response);
			exit($this->response->send());
		}
		$this->_input = $this->request->getJsonRawBody();
	}

	function beforeExecuteRoute() {
		try {
			$access_token   = str_replace('Bearer ', '', filter_input(INPUT_SERVER, 'Authorization'));
			$merchant_token = $this->dispatcher->getParam('merchant_token', 'string');
			if (!$access_token) {
				throw new Exception(static::INVALID_API_KEY_MESSAGE);
			}
			if ($merchant_token && !($this->_premium_merchant = User::findFirst(['status = 1 AND premium_merchant = 1 AND role_id = ?0 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]))) {
				throw new Exception(static::INVALID_API_KEY_MESSAGE);
			}
			$encrypted_data = strtr($access_token, ['-' => '+', '_' => '/', ',' => '=']);
			$crypt          = new Crypt;
			$payload        = json_decode($crypt->decryptBase64($encrypted_data, $this->config->encryption_key));
			$params         = $this->_premium_merchant
					? ['status = 1 AND api_key = ?0 AND ((role_id = ?1 AND id = ?2) OR (role_id = ?3 AND merchant_id = ?4))', 'bind' => [$payload->api_key, Role::MERCHANT, $this->_premium_merchant->id, Role::BUYER, $this->_premium_merchant->id]]
					: ['status = 1 AND merchant_token IS NULL AND merchant_id IS NULL AND role_id > 2 AND api_key = ?0', 'bind' => [$payload->api_key]];
			if (($merchant_token && $payload->merchant_token != $merchant_token) || !($this->_current_user = User::findFirst($params))) {
				throw new Exception(static::INVALID_API_KEY_MESSAGE);
			}
		} catch (Exception $e) {
			$this->_response['invalid_api_key'] = 1;
			$this->_response['message']         = $e->getMessage();
			$this->response->setJsonContent($this->_response);
			exit($this->response->send());
		}
	}

	protected function _setPaginationRange($total_pages, $current_page = 1) : array {
		if ($total_pages < 2) {
			return [];
		}
		$start_page = min(max($current_page - 3, 1), $total_pages);
		$end_page   = max(min($current_page + 3, $total_pages), 1);
		$pages      = range($start_page, $end_page);
		if ($start_page > 1) {
			array_unshift($pages, 1);
		}
		if ($end_page < $total_pages) {
			$pages[] = $total_pages;
		}
		return $pages;
	}
}
