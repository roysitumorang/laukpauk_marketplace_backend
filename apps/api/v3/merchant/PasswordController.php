<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Device;
use Application\Models\User;
use Exception;

class PasswordController extends ControllerBase {
	function saveAction() {
		try {
			if (!$this->post->old_password) {
				throw new Exception('Password Lama harus diisi.');
			}
			if (!$this->security->checkHash($this->post->old_password, $this->currentUser->password)) {
				throw new Exception('Password Lama salah.');
			}
			if (!$this->post->new_password) {
				throw new Exception('Password Baru harus diisi.');
			}
			$this->currentUser->setNewPassword($this->post->new_password);
			$this->currentUser->setNewPasswordConfirmation($this->post->new_password);
			if (!$this->currentUser->validation() || !$this->currentUser->update()) {
				throw new Exception('Ganti password tidak berhasil!');
			}
			if ($this->post->device_token) {
				if (strlen($this->post->device_token) === 36 && !$this->currentUser->device_token) {
					$device = Device::findFirstByToken($this->post->device_token);
					if (!$device) {
						$device             = new Device;
						$device->user_id    = $this->currentUser->id;
						$device->token      = $this->post->device_token;
						$device->created_by = $this->currentUser->id;
						$device->create();
					} else if ($device->user_id != $this->currentUser->id) {
						$device->user_id    = $this->currentUser->id;
						$device->updated_by = $this->currentUser->id;
						$device->update();
					}
				} else if ($this->post->device_token != $this->currentUser->device_token) {
					$old_owner = User::findFirstByDeviceToken($this->post->device_token);
					if ($old_owner) {
						$old_owner->update(['device_token' => null]);
					}
					$this->currentUser->update(['device_token' => $this->post->device_token]);
					$this->currentUser->getDevices()->delete();
				}
			}
			$this->_response['status'] = 1;
			throw new Exception('Ganti password berhasil!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function sendResetTokenAction() {
		try {
			if (!$this->post->mobile_phone || !($user = User::findFirst(['role_id = ?0 AND mobile_phone = ?1', 'bind' => [Role::MERCHANT, $this->post->mobile_phone]]))) {
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
			if (!$this->post->password_reset_token || !($user = User::findFirst(['role_id = ?0 AND password_reset_token = ?1 AND status = 1', 'bind' => [Role::MERCHANT, $this->post->password_reset_token]]))) {
				throw new Exception('Token reset password tidak valid!.');
			}
			if (!$this->post->new_password) {
				throw new Exception('Password baru harus diisi.');
			}
			if ($this->post->device_token) {
				if (strlen($this->post->device_token) === 36 && !$user->device_token) {
					$device = Device::findFirstByToken($this->post->device_token);
					if (!$device) {
						$device             = new Device;
						$device->token      = $this->post->device_token;
						$device->user_id    = $user->id;
						$device->created_by = $user->id;
						$device->create();
					} else if ($device->user_id != $user->id) {
						$device->user_id    = $user->id;
						$device->updated_by = $user->id;
						$device->update();
					}
				} else if ($this->post->device_token != $user->device_token) {
					$old_owner = User::findFirstByDeviceToken($this->post->device_token);
					if ($old_owner) {
						$old_owner->update(['device_token' => null]);
					}
					$user->update(['device_token' => $this->post->device_token]);
					$user->getDevices()->delete();
				}
			}
			if (!$user->resetPassword($this->post->new_password)) {
				throw new Exception('Reset password tidak berhasil!');
			}
			$this->_response['status']               = 1;
			$this->_response['data']['access_token'] = $this->jsonWebToken->encode(['api_key' => $user->api_key]);
			$this->_response['data']['current_user'] = [
				'id'           => $user->id,
				'name'         => $user->name,
				'role'         => $user->role->name,
				'mobile_phone' => $user->mobile_phone,
				'address'      => $user->address,
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
				'open_on_sunday'        => $user->open_on_sunday,
				'open_on_monday'        => $user->open_on_monday,
				'open_on_tuesday'       => $user->open_on_tuesday,
				'open_on_wednesday'     => $user->open_on_wednesday,
				'open_on_thursday'      => $user->open_on_thursday,
				'open_on_friday'        => $user->open_on_friday,
				'open_on_saturday'      => $user->open_on_saturday,
				'business_opening_hour' => strval($user->business_opening_hour),
				'business_closing_hour' => strval($user->business_closing_hour),
				'merchant_note'         => $user->merchant_note,
				'minimum_purchase'      => $user->minimum_purchase,
				'delivery_hours'        => array_fill_keys($user->delivery_hours ?: range($user->business_opening_hour, $user->business_closing_hour), 1),
			];
			throw new Exception('Reset password berhasil!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}