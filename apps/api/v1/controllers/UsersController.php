<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Phalcon\Crypt;

class UsersController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($this->dispatcher->getActionName() === 'update') {
			parent::beforeExecuteRoute();
		}
	}

	function onConstruct() {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			exit($this->response->send());
		}
	}

	function createAction() {
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
		$user = User::findFirstByActivationToken($activation_token);
		if (!$user) {
			$this->_response['message'] = 'Token aktivasi tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$user->activate();
		$crypt                      = new Crypt;
		$this->_response['status']  = 1;
		$this->_response['message'] = 'Aktivasi account berhasil!';
		$this->_response['data']    = [
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
				'village_id'            => $user->village->id,
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
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function updateAction() {
		$this->_current_user->setName($this->_input->name);
		$this->_current_user->setMobilePhone($this->_input->mobile_phone);
		$this->_current_user->setAddress($this->_input->address);
		$this->_current_user->village = Village::findFirst($this->input->village_id);
		$this->_current_user->business_opening_hour = $this->_current_user->business_opening_hour;
		$this->_current_user->business_closing_hour = $this->_current_user->business_closing_hour;
		$this->_current_user->setOpenOnSunday($this->_input->open_on_sunday);
		$this->_current_user->setOpenOnMonday($this->_input->open_on_monday);
		$this->_current_user->setOpenOnTuesday($this->_input->open_on_tuesday);
		$this->_current_user->setOpenOnWednesday($this->_input->open_on_wednesday);
		$this->_current_user->setOpenOnThursday($this->_input->open_on_thursday);
		$this->_current_user->setOpenOnFriday($this->_input->open_on_friday);
		$this->_current_user->setOpenOnSaturday($this->_input->open_on_saturday);
		if ($this->_current_user->validation() && $this->_current_user->update()) {
			$this->_response['status']  = 1;
			$this->_response['message'] = 'Update profile berhasil!';
			$this->_response['data']    = [
				'current_user' => [
					'id'                    => $this->_current_user->id,
					'name'                  => $this->_current_user->name,
					'mobile_phone'          => $this->_current_user->mobile_phone,
					'address'               => $this->_current_user->address,
					'subdistrict_id'        => $this->_current_user->village->subdistrict->id,
					'village_id'            => $this->_current_user->village->id,
					'role'                  => $this->_current_user->role->name,
					'open_on_sunday'        => $this->_current_user->open_on_sunday,
					'open_on_monday'        => $this->_current_user->open_on_monday,
					'open_on_tuesday'       => $this->_current_user->open_on_tuesday,
					'open_on_wednesday'     => $this->_current_user->open_on_wednesday,
					'open_on_thursday'      => $this->_current_user->open_on_thursday,
					'open_on_friday'        => $this->_current_user->open_on_friday,
					'open_on_saturday'      => $this->_current_user->open_on_saturday,
					'business_opening_hour' => $this->_current_user->business_opening_hour,
					'business_closing_hour' => $this->_current_user->business_closing_hour,
				]
			];
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$this->_response['message']        = 'Update profile gagal! Silahkan cek form dan coba lagi.';
		$this->_response['data']['errors'] = [];
		foreach ($user->getMessages() as $error) {
			$this->_response['data']['errors'][$error->getField()] = $error->getMessage();
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}