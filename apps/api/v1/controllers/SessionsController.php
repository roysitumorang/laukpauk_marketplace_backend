<?php

namespace Application\Api\V1\Controllers;

use Application\Models\BannerCategory;
use Application\Models\Device;
use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Crypt;

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
			$this->_response['status']          = 1;
			$this->_response['data']['banners'] = $banners;
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
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
			$this->_response['data']['errors'] = $errors;
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$user = User::findFirst(['status = 1 AND role_id IN ({role_ids:array}) AND mobile_phone = :mobile_phone:', 'bind' => [
			'role_ids'     => [Role::BUYER, Role::MERCHANT],
			'mobile_phone' => $this->_input->mobile_phone,
		]]);
		if ($user && $this->security->checkHash($this->_input->password, $user->password)) {
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
			$this->_response['status'] = 1;
			$this->_response['data']   = [
				'access_token' => strtr($crypt->encryptBase64($user->api_key, $this->config->encryption_key), [
					'+' => '-',
					'/' => '_',
					'=' => ',',
				]),
				'current_user' => [
					'id'                    => $user->id,
					'name'                  => $user->name,
					'mobile_phone'          => $user->mobile_phone,
					'address'               => $user->address,
					'subdistrict_id'        => $user->village->subdistrict->id,
					'subdistrict'           => $user->village->subdistrict->name,
					'village_id'            => $user->village->id,
					'village'               => $user->village->name,
					'role'                  => $user->role->name,
					'open_on_sunday'        => $user->open_on_sunday,
					'open_on_monday'        => $user->open_on_monday,
					'open_on_tuesday'       => $user->open_on_tuesday,
					'open_on_wednesday'     => $user->open_on_wednesday,
					'open_on_thursday'      => $user->open_on_thursday,
					'open_on_friday'        => $user->open_on_friday,
					'open_on_saturday'      => $user->open_on_saturday,
					'business_opening_hour' => $user->business_opening_hour,
					'business_closing_hour' => $user->business_closing_hour,
				]
			];
		} else {
			$this->_response['message'] = 'Nomor HP dan/atau password salah!';
		}
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}