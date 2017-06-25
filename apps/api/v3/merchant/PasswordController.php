<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Device;
use Application\Models\User;
use Error;
use Phalcon\Crypt;

class PasswordController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($this->dispatcher->getActionName() === 'save') {
			parent::beforeExecuteRoute();
		}
	}

	function saveAction() {
		try {
			if (!$this->_post->old_password) {
				throw new Error('Password Lama harus diisi.');
			}
			if (!$this->security->checkHash($this->_post->old_password, $this->_current_user->password)) {
				throw new Error('Password Lama salah.');
			}
			if (!$this->_post->new_password) {
				throw new Error('Password Baru harus diisi.');
			}
			$this->_current_user->setNewPassword($this->_post->new_password);
			$this->_current_user->setNewPasswordConfirmation($this->_post->new_password);
			if ($this->_current_user->validation() && $this->_current_user->update()) {
				if ($this->_post->device_token) {
					$device = Device::findFirstByToken($this->_post->device_token);
					if (!$device) {
						$device             = new Device;
						$device->user_id    = $this->_current_user->id;
						$device->token      = $this->_post->device_token;
						$device->created_by = $this->_current_user->id;
						$device->create();
					} else if ($device->user_id != $this->_current_user->id) {
						$device->user_id    = $this->_current_user->id;
						$device->updated_by = $this->_current_user->id;
						$device->update();
					}
				}
				$this->_response['status'] = 1;
				throw new Error('Ganti password berhasil!');
			}
			throw new Error('Ganti password tidak berhasil!');
		} catch (Error $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function sendResetTokenAction() {
		try {
			if (!$this->_post->mobile_phone || !($user = User::findFirst(['mobile_phone = ?0 AND ' . ($this->_premium_merchant ? "(merchant_id = {$this->_premium_merchant->id} OR id = {$this->_premium_merchant->id})" : 'merchant_id IS NULL'), 'bind' => [$this->_post->mobile_phone]]))) {
				throw new Error('No HP tidak terdaftar!');
			}
			if ($user->status == -1) {
				throw new Error('Akun Anda telah dinonaktifkan!');
			}
			$user->sendPasswordResetToken();
			$this->_response['status'] = 1;
			throw new Error('Token password telah dikirim via sms!');
		} catch (Error $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function resetAction() {
		try {
			if (!$this->_post->password_reset_token || !($user = User::findFirst(['password_reset_token = ?0 AND status = 1', 'bind' => [$this->_post->password_reset_token]]))) {
				throw new Error('Token reset password tidak valid!.');
			}
			if (!$this->_post->new_password) {
				throw new Error('Password baru harus diisi.');
			}
			if ($user->resetPassword($this->_post->new_password)) {
				if ($this->_post->device_token) {
					$device = Device::findFirstByToken($this->_post->device_token);
					if (!$device) {
						$device             = new Device;
						$device->user_id    = $user->id;
						$device->token      = $this->_post->device_token;
						$device->created_by = $user->id;
						$device->create();
					} else if ($device->user_id != $user->id) {
						$device->user_id    = $user->id;
						$device->updated_by = $user->id;
						$device->update();
					}
				}
				$crypt        = new Crypt;
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
				$this->_response['status'] = 1;
				$this->_response['data']   = [
					'access_token' => strtr($crypt->encryptBase64(json_encode($payload), $this->config->encryption_key), [
						'+' => '-',
						'/' => '_',
						'=' => ',',
					]),
					'current_user' => $current_user,
				];
				throw new Error('Reset password berhasil!');
			}
			throw new Error('Reset password tidak berhasil!');
		} catch (Error $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}