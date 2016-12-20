<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;

class UsersController extends ControllerBase {
	function createAction() {
		if (!$this->request->isPost() || $this->_access_token->user) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$village_id    = filter_var($this->_input->village_id, FILTER_VALIDATE_INT);
		$user          = new User;
		$user->village = Village::findFirst($village_id);
		$user->setName($this->_input->name);
		$user->setNewPassword($this->_input->new_password);
		$user->setNewPasswordConfirmation($this->_input->new_password);
		$user->setMobilePhone($this->_input->mobile_phone);
		$user->setDeposit(0);
		$user->role_id = Role::findFirstByName('Buyer')->id;
		if ($user->validation() && $user->create()) {
			$this->_response['status']                   = 1;
			$this->_response['data']['activation_token'] = $user->activation_token;
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$this->_response['message']        = 'Registrasi gagal! Silahkan cek form dan coba lagi.';
		$this->_response['data']['errors'] = [];
		foreach ($user->getMessages() as $error) {
			$this->_response['data']['errors'][$error->getField()] = $error->getMessage();
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function activateAction($activation_token) {
		if (!$this->request->isPost() || $this->_access_token->user) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$user = User::findFirstByActivationToken($activation_token);
		if (!$user) {
			$this->_response['message'] = 'Token aktivasi tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$user->activate();
		$this->_access_token->user  = $user;
		$this->_access_token->save();
		$this->_response['status']  = 1;
		$this->_response['message'] = 'Aktivasi account berhasil!';
		$this->_response['data']    = [
			'current_user' => [
				'id'             => $user->id,
				'name'           => $user->name,
				'mobile_phone'   => $user->mobile_phone,
				'address'        => $user->address,
				'subdistrict_id' => $user->village->subdistrict->id,
				'village_id'     => $user->village->id,
				'is_buyer'       => 1,
			]
		];
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}