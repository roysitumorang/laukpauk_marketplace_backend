<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Role;
use Application\Models\Setting;
use Exception;
use Phalcon\Mvc\Controller;

abstract class ControllerBase extends Controller {
	const INVALID_API_ENDPOINT_MESSAGE = 'API endpoint tidak valid, silahkan instal ulang aplikasi Anda.';
	const OFFLINE_MESSAGE              = 'Mohon maaf, server sedang dalam maintenance.';
	const EXPIRED_TOKEN_MESSAGE        = 'Token expired, silahkan logout dan login kembali.';
	protected $_response = [
		'status' => -1,
		'data'   => [],
	];

	function onConstruct() {
		try {
			$current_user = $this->currentUser;
			register_shutdown_function(function() use($current_user) {
				$this->db->insertAsDict('api_calls', [
					'user_id'        => $current_user ? $current_user->id : null,
					'url'            => $this->request->getServer('REQUEST_URI'),
					'request_method' => $this->request->getMethod(),
					'ip_address'     => $this->request->getServer('REMOTE_ADDR'),
					'user_agent'     => $this->request->getServer('HTTP_USER_AGENT'),
					'execution_time' => (microtime(true) - $this->request->getServer('REQUEST_TIME_FLOAT')) * 1000,
					'memory_usage'   => memory_get_peak_usage(true) / 1048576,
					'created_at'     => $this->currentDatetime->format('Y-m-d H:i:s'),
				]);
			});
			if ($current_user && $current_user->role_id != Role::MERCHANT) {
				throw new Exception(static::INVALID_API_ENDPOINT_MESSAGE);
			}
			if (Setting::findFirstByName('maintenance_mode')->value) {
				$this->_response['offline'] = 1;
				throw new Exception(static::OFFLINE_MESSAGE);
			}
		} catch (Exception $e) {
			$this->_response['message'] = call_user_func(function() use($e) {
				$message = $e->getMessage();
				switch ($message) {
					case 'Expired token':
						return static::EXPIRED_TOKEN_MESSAGE;
					case static::INVALID_API_ENDPOINT_MESSAGE:
					case static::OFFLINE_MESSAGE:
						return $message;
					default:
						return 'Token tidak valid, silahkan logout dan login kembali.';
				}
			});
			$this->response->setJsonContent($this->_response);
			$this->response->send();
			exit;
		}
		$latest_version = $this->db->fetchColumn("SELECT a.version FROM releases a WHERE a.user_type = 'merchant' AND NOT EXISTS(SELECT 1 FROM releases b WHERE b.user_type = a.user_type AND b.id > a.id)");
		if ($latest_version != $this->request->getServer('HTTP_APP_VERSION')) {
			$this->_response['message'] = "Tersedia update versi {$latest_version}, silahkan download di Play Store dan instal.";
		}
		$this->_response['data']['total_new_orders']        = $current_user ? $current_user->totalNewOrders() : 0;
		$this->_response['data']['total_new_notifications'] = $current_user ? $current_user->totalNewNotifications() : 0;
	}
}