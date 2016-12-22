<?php

namespace Application\Api\V1\Controllers;

use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Crypt;

class SessionsController extends ControllerBase {
	function beforeExecuteRoute() {}

	function createAction() {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
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
			$this->_response['status'] = 1;
			$this->_response['data']   = [
				'access_token' => strtr($crypt->encryptBase64($user->api_key, $this->config->encryption_key), [
					'+' => '-',
					'/' => '_',
					'=' => ',',
				]),
				'current_user' => [
					'id'             => $user->id,
					'name'           => $user->name,
					'mobile_phone'   => $user->mobile_phone,
					'address'        => $user->address,
					'subdistrict_id' => $user->village->subdistrict->id,
					'village_id'     => $user->village->id,
					'role'           => $user->role->name,
				]
			];
		} else {
			$this->_response['message'] = 'Nomor HP dan/atau password salah!';
		}
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}