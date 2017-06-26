<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Device;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Crypt;
use Phalcon\Exception;

class PasswordController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($this->dispatcher->getActionName() === 'save') {
			parent::beforeExecuteRoute();
		}
	}

	function saveAction() {
		try {
			if (!$this->_post->old_password) {
				throw new Exception('Password Lama harus diisi.');
			}
			if (!$this->security->checkHash($this->_post->old_password, $this->_current_user->password)) {
				throw new Exception('Password Lama salah.');
			}
			if (!$this->_post->new_password) {
				throw new Exception('Password Baru harus diisi.');
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
				throw new Exception('Ganti password berhasil!');
			}
			throw new Exception('Ganti password tidak berhasil!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function sendResetTokenAction() {
		try {
			if (!$this->_post->mobile_phone || !($user = User::findFirst(['role_id = ?0 AND mobile_phone = ?1 AND merchant_id ' . ($this->_premium_merchant ? "= {$this->_premium_merchant->id}" : 'IS NULL'), 'bind' => [Role::BUYER, $this->_post->mobile_phone]]))) {
				throw new Exception('No HP tidak terdaftar!');
			}
			if ($user->status == -1) {
				throw new Exception('Akun Anda telah dinonaktifkan!');
			}
			$user->sendPasswordResetToken();
			$this->_response['status'] = 1;
			throw new Exception('Token password telah dikirim via sms!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function resetAction() {
		try {
			if (!$this->_post->password_reset_token || !($user = User::findFirst(['status = 1 AND role_id = ?0 AND password_reset_token = ?1', 'bind' => [Role::BUYER, $this->_post->password_reset_token]]))) {
				throw new Exception('Token reset password tidak valid!.');
			}
			if (!$this->_post->new_password) {
				throw new Exception('Password baru harus diisi.');
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
				$payload = ['api_key' => $user->api_key];
				if ($merchant_token) {
					$payload['merchant_token'] = $merchant_token;
				}
				$this->_response['status']               = 1;
				$this->_response['data']['access_token'] = strtr($crypt->encryptBase64(json_encode($payload), $this->config->encryption_key), [
					'+' => '-',
					'/' => '_',
					'=' => ',',
				]);
				$this->_response['data']['current_user'] = $current_user;
				throw new Exception('Reset password berhasil!');
			}
			throw new Exception('Reset password tidak berhasil!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}