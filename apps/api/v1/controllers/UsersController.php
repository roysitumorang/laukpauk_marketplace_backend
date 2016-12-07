<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;

class UsersController extends BaseController {
	function createAction() {
		if (!$this->request->isPost() || $this->_access_token->user) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$village_id    = $this->request->getPost('village_id', 'int');
		$user          = new User;
		$user->role    = Role::find(Role::BUYER);
		$user->village = Village::findFirst($village_id);
		$user->setName($this->request->getPost('name'));
		$user->setNewPassword($this->request->getPost('new_password'));
		$user->setNewPasswordConfirmation($this->request->getPost('new_password'));
		$user->setPhone($this->request->getPost('phone'));
		$user->setDeposit(0);
		$user->setBuyPoint(0);
		$user->setAffiliatePoint(0);
		$user->setReward(0);
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
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}