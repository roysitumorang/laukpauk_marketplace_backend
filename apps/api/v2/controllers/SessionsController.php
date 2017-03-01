<?php

namespace Application\Api\V2\Controllers;

use Application\Models\BannerCategory;
use Application\Models\Device;
use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Crypt;
use Phalcon\Db;

class SessionsController extends ControllerBase {
	function beforeExecuteRoute() {}

	function createAction() {
		if (!$this->request->isPost()) {
			$banners  = [];
			$category = BannerCategory::findFirstByName('Login');
			foreach ($category->banners as $banner) {
				if ($banner->published) {
					$banners[] = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/assets/image/' . $banner->file_name;
				}
			}
			$this->_response = [
				'status' => 1,
				'data'   => ['banners' => $banners],
			];
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$merchant_token = $this->dispatcher->getParam('merchant_token', 'string');
		if ($merchant_token && !($premium_merchant = $this->db->fetchOne('SELECT * FROM users WHERE status = 1 AND premium_merchant = 1 AND role_id = ? AND merchant_token = ?', Db::FETCH_OBJ, [Role::MERCHANT, $merchant_token]))) {
			$this->response->setJsonContent(['message' => 'Merchant token tidak valid, silahkan hubungi Tim LaukPauk.id!']);
			return $this->response;
		}
		$errors = [];
		if (!$this->_input->mobile_phone) {
			$errors['mobile_phone'] = 'nomor HP harus diisi';
		}
		if (!$this->_input->password) {
			$errors['password'] = 'password harus diisi';
		}
		if ($errors) {
			$this->_response['message'] = implode('<br>', $errors);
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$params = $premium_merchant
			? ['status = 1 AND mobile_phone = ?0 AND ((role_id = ?1 AND merchant_id = ?2) OR (role_id = ?3 AND merchant_id = ?4))', 'bind' => [$this->_input->mobile_phone, Role::MERCHANT, $premium_merchant->id, Role::BUYER, $premium_merchant->id]]
			: ['status = 1 AND merchant_token IS NULL AND merchant_id IS NULL AND role_id IN ({role_ids:array}) AND mobile_phone = :mobile_phone:', 'bind' => ['role_ids' => [Role::BUYER, Role::MERCHANT], 'mobile_phone' => $this->_input->mobile_phone]];
		$user = User::findFirst($params);
		if (!$user || !$this->security->checkHash($this->_input->password, $user->password)) {
			$this->_response['message'] = 'Nomor HP dan/atau password salah!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$crypt                  = new Crypt;
		$login_history          = new LoginHistory;
		$login_history->user_id = $user->id;
		$login_history->create();
		if ($this->_input->device_token) {
			$device = Device::findFirstByToken($this->_input->device_token);
			if (!$device) {
				$device             = new Device;
				$device->user       = $user;
				$device->token      = $this->_input->device_token;
				$device->created_by = $user->id;
				$device->create();
			} else if ($device->user_id != $this->_current_user->id) {
				$device->user       = $user;
				$device->updated_by = $user->id;
				$device->update();
			}
		}
		$current_user = [
			'id'           => $user->id,
			'name'         => $user->name,
			'role'         => $user->role->name,
			'mobile_phone' => $user->mobile_phone,
			'address'      => $user->address,
			'subdistrict'  => [
				'id'   => $user->village->subdistrict->id,
				'name' => $user->village->subdistrict->name,
			],
			'village'      => [
				'id'   => $user->village->id,
				'name' => $user->village->name,
			],
			'subdistrict'  => [
				'id'   => $user->village->subdistrict->id,
				'name' => $user->village->subdistrict->name,
			],
			'city'         => [
				'id'   => $user->village->subdistrict->city->id,
				'name' => $user->village->subdistrict->city->name,
			],
			'province'     => [
				'id'   => $user->village->subdistrict->city->province->id,
				'name' => $user->village->subdistrict->city->province->name,
			],
		];
		if ($user->role->name === 'Merchant') {
			$current_user['open_on_sunday']        = $user->open_on_sunday;
			$current_user['open_on_monday']        = $user->open_on_monday;
			$current_user['open_on_tuesday']       = $user->open_on_tuesday;
			$current_user['open_on_wednesday']     = $user->open_on_wednesday;
			$current_user['open_on_thursday']      = $user->open_on_thursday;
			$current_user['open_on_friday']        = $user->open_on_friday;
			$current_user['open_on_saturday']      = $user->open_on_saturday;
			$current_user['business_opening_hour'] = $user->business_opening_hour;
			$current_user['business_closing_hour'] = $user->business_closing_hour;
			$current_user['minimum_purchase']      = $user->minimum_purchase;
			$current_user['delivery_hours']        = array_fill_keys($user->delivery_hours ?: range($user->business_opening_hour, $user->business_closing_hour), 1);
		}
		$payload = ['api_key' => $user->api_key];
		if ($merchant_token) {
			$payload['merchant_token'] = $merchant_token;
		}
		$this->_response = [
			'status' => 1,
			'data'   => [
				'access_token' => strtr($crypt->encryptBase64(json_encode($payload), $this->config->encryption_key), [
					'+' => '-',
					'/' => '_',
					'=' => ',',
				]),
				'current_user' => $current_user,
			],
		];
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}