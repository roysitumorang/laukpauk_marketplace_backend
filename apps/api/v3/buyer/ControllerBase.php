<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Role;
use Application\Models\Setting;
use Application\Models\User;
use IntlDateFormatter;
use Phalcon\Crypt;
use Phalcon\Exception;
use Phalcon\Mvc\Controller;

abstract class ControllerBase extends Controller {
	const INVALID_API_KEY_MESSAGE = 'API key tidak valid, silahkan logout dan login kembali!';
	protected $_response = [
		'status' => -1,
		'data'   => [],
	];
	protected $_current_user;
	protected $_premium_merchant;
	protected $_post;
	protected $_server;
	protected $_date_formatter;

	function initialize() {
		register_shutdown_function(function() {
			$this->db->insertAsDict('api_calls', [
				'user_id'        => $this->_current_user->id,
				'url'            => $this->request->getServer('REQUEST_URI'),
				'request_method' => $this->request->getMethod(),
				'ip_address'     => $this->request->getServer('REMOTE_ADDR'),
				'user_agent'     => $this->request->getServer('HTTP_USER_AGENT'),
				'execution_time' => (microtime(true) - $this->request->getServer('REQUEST_TIME_FLOAT')) * 1000,
				'memory_usage'   => memory_get_peak_usage(true) / 1048576,
				'created_at'     => $this->currentDatetime->format('Y-m-d H:i:s'),
			]);
		});
		if (Setting::findFirstByName('maintenance_mode')->value) {
			$this->_response['offline'] = 1;
			$this->response->setJsonContent($this->_response);
			$this->response->send();
			exit;
		}
		$this->_post           = $this->request->getJsonRawBody();
		$this->_server         = json_decode($this->request->getServer('HTTP_USER_DATA'));
		$this->_date_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'd MMM yyyy'
		);
	}

	function beforeExecuteRoute() {
		try {
			$access_token   = str_replace('Bearer ', '', filter_input(INPUT_SERVER, 'Authorization'));
			$merchant_token = $this->dispatcher->getParam('merchant_token', 'string');
			if (!$access_token) {
				throw new Exception(static::INVALID_API_KEY_MESSAGE);
			}
			if ($merchant_token && !($this->_premium_merchant = User::findFirst(['status = 1 AND role_id = ?0 AND premium_merchant = 1 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]))) {
				throw new Exception(static::INVALID_API_KEY_MESSAGE);
			}
			$encrypted_data = strtr($access_token, ['-' => '+', '_' => '/', ',' => '=']);
			$crypt          = new Crypt;
			$payload        = json_decode($crypt->decryptBase64($encrypted_data, $this->config->encryption_key));
			$params         = $this->_premium_merchant
					? ['status = 1 AND role_id = ?0 AND api_key = ?1 AND merchant_id = ?2', 'bind' => [Role::BUYER, $payload->api_key, $this->_premium_merchant->id]]
					: ['status = 1 AND role_id = ?0 AND api_key = ?1 AND merchant_id IS NULL', 'bind' => [Role::BUYER, $payload->api_key]];
			if (($merchant_token && $payload->merchant_token != $merchant_token) || !($this->_current_user = User::findFirst($params))) {
				throw new Exception(static::INVALID_API_KEY_MESSAGE);
			}
			$this->_response['version'] = $this->db->fetchColumn("SELECT MAX(version) FROM releases WHERE user_type = 'buyer' AND application_type = '" . ($this->_premium_merchant ? 'premium' : 'free') . "'");
		} catch (Exception $e) {
			$this->_response['invalid_api_key'] = 1;
			$this->_response['message']         = $e->getMessage();
			$this->response->setJsonContent($this->_response);
			$this->response->send();
			exit;
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
