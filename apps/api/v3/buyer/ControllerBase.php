<?php

namespace Application\Api\V3\Buyer;

use Application\Models\{Role, Setting, User};
use IntlDateFormatter;
use Phalcon\Crypt;
use Phalcon\Mvc\Controller;

abstract class ControllerBase extends Controller {
	const INVALID_API_KEY_MESSAGE = 'API key tidak valid, silahkan logout dan login kembali!';
	protected $_response = [
		'status' => -1,
		'data'   => [],
	];
	protected $_current_user;
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
			if (!$access_token = strtr(filter_input(INPUT_SERVER, 'Authorization'), ['Bearer ' => ''])) {
				throw new \Exception(static::INVALID_API_KEY_MESSAGE);
			}
			$encrypted_data = strtr($access_token, ['-' => '+', '_' => '/', ',' => '=']);
			$crypt          = new Crypt;
			$payload        = json_decode($crypt->decryptBase64($encrypted_data, $this->config->encryption_key));
			if (!$this->_current_user = User::findFirst(['status = 1 AND role_id = ?0 AND api_key = ?1', 'bind' => [Role::BUYER, $payload->api_key]])) {
				throw new \Exception(static::INVALID_API_KEY_MESSAGE);
			}
			$this->_response['version'] = $this->db->fetchColumn("SELECT a.version FROM releases a WHERE a.user_type = 'buyer' AND NOT EXISTS(SELECT 1 FROM releases b WHERE b.user_type = a.user_type AND b.id > a.id)");
		} catch (\Exception $e) {
			$this->_response['invalid_api_key'] = 1;
			$this->_response['message']         = $e->getMessage();
			$this->response->setJsonContent($this->_response);
			$this->response->send();
			exit;
		}
	}
}
