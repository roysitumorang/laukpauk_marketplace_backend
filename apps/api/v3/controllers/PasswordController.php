<?php

namespace Application\Api\V3\Controllers;

use Error;

class PasswordController extends ControllerBase {
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
				$this->_response['status']  = 1;
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
}