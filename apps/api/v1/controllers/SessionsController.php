<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Role;
use Application\Models\User;

class SessionsController extends ControllerBase {
	function createAction() {
		if (!$this->request->isPost() || $this->_access_token->user) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$errors = [];
		if (!$this->_input->phone) {
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
		$user = User::findFirst([
			'conditions' => 'status = :status: AND role_id IN ({role_ids:array}) AND phone = :phone:',
			'bind'       => [
				'status'   => array_search('ACTIVE', User::STATUS),
				'role_ids' => [Role::BUYER, Role::MERCHANT],
				'phone'    => $this->_input->phone,
			],
		]);
		if ($user && password_verify($this->_input->password, $user->password)) {
			$this->_access_token->user  = $user;
			$this->_access_token->save();
			$this->_response['status']  = 1;
			$this->_response['data']    = [
				'current_user' => [
					'id'             => $user->id,
					'name'           => $user->name,
					'phone'          => $user->phone,
					'address'        => $user->address,
					'subdistrict_id' => $user->village->subdistrict->id,
					'village_id'     => $user->village->id,
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