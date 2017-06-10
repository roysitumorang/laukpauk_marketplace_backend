<?php

namespace Application\Api\V3\Controllers;

use Application\Models\User;
use Error;

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
				$this->_response['status'] = 1;
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