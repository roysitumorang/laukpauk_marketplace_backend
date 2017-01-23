<?php

namespace Application\Backend\Controllers;

class PasswordController extends ControllerBase {
	function createAction() {
		$this->view->menu = $this->_menu('Dashboard');
		if (!$this->request->isPost()) {
			return;
		}
		$password = $this->request->getPost('password', 'string');
		$errors   = [];
		$this->currentUser->setChangePassword(true);
		$this->currentUser->setNewPassword($this->request->getPost('new_password'));
		$this->currentUser->setNewPasswordConfirmation($this->request->getPost('new_password_confirmation'));
		if (!$password) {
			$errors[] = 'password lama masih kosong';
		} else if (!$this->security->checkHash($password, $this->currentUser->password)) {
			$errors[] = 'password lama salah';
		}
		if (!$this->currentUser->validation()) {
			foreach ($this->currentUser->getMessages() as $error) {
				$errors[] = $error;
			}
		}
		if (!$errors && $this->currentUser->update()) {
			$this->flashSession->success('Password baru berhasil disimpan.');
			return;
		}
		$this->flashSession->error('Password baru tidak berhasil disimpan, silahkan cek form dan coba lagi.');
		foreach ($errors as $error) {
			$this->flashSession->error($error);
		}
	}
}
