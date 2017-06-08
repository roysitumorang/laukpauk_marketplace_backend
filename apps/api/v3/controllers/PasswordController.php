<?php

namespace Application\Api\V3\Controllers;

class PasswordController extends ControllerBase {
	function saveAction() {
		if (!$this->_post->old_password) {
			$this->_response['message'] = 'Password Lama harus diisi.';
		} else if (!$this->security->checkHash($this->_post->old_password, $this->_current_user->password)) {
			$this->_response['message'] = 'Password Lama salah.';
		} else if (!$this->_post->new_password) {
			$this->_response['message'] = 'Password Baru harus diisi.';
		} else if (!$this->_current_user->validation() || !$this->_current_user->update()) {
			$errors = [];
			foreach ($this->_current_user->getMessages() as $error) {
				$errors[] = $error->getMessage();
			}
			$this->_response['message'] = implode('<br>', $errors);
		} else {
			$this->_response['status']  = 1;
			$this->_response['message'] = 'Ganti password berhasil!';
		}
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

}