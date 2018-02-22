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
		$gps_coordinates = json_decode($this->request->getServer('HTTP_GPS_COORDINATES'), true);
		if ($current_user && $gps_coordinates && ($current_user->latitude != $gps_coordinates['latitude'] || $current_user->longitude != $gps_coordinates['longitude'])) {
			$current_user->update($gps_coordinates);
		}
		$this->_response['data']['total_new_orders']        = $current_user ? $current_user->totalNewOrders() : 0;
		$this->_response['data']['total_new_notifications'] = $current_user ? $current_user->totalNewNotifications() : 0;
	}
}