<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Role;
use Application\Models\User;

class SessionsController extends ControllerBase {
	function createAction() {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$errors = [];
		if (!$this->_input->mobile_phone) {
			$errors['phone'] = 'nomor HP harus diisi';
		}
		if (!$this->_input->password) {
			$errors['password'] = 'password harus diisi';
		}
		if ($errors) {
			$this->_response['data']['errors'] = $errors;
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$user = User::findFirst(['status = 1 AND role_id IN ({?0|array}) AND mobile_phone = ?1', 'bind' => [
			[Role::BUYER, Role::MERCHANT],
			$this->_input->mobile_phone,
		]]);
		if ($user && $this->security->checkHash($this->_input->password, $user->password)) {
			if (!$this->_access_token->user) {
				$this->_access_token->user = $user;
				$this->_access_token->save();
			}
			$this->_response['status'] = 1;
			$this->_response['data']   = [
				'current_user' => [
					'id'             => $this->_access_token->user->id,
					'name'           => $this->_access_token->user->name,
					'mobile_phone'   => $this->_access_token->user->mobile_phone,
					'address'        => $this->_access_token->user->address,
					'subdistrict_id' => $this->_access_token->user->village->subdistrict->id,
					'village_id'     => $this->_access_token->user->village->id,
					'is_buyer'       => $this->_access_token->user->role->name == 'Buyer',
					'is_merchant'    => $this->_access_token->user->role->name == 'Merchant',
				]
			];
		} else {
			$this->_response['message'] = 'Nomor HP dan/atau password salah!';
		}
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function deleteAction() {
		if (!$this->request->isPost() || !$this->_access_token->user) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$this->_access_token->delete();
		$this->_response['status'] = 1;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}