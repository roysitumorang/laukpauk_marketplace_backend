<?php

namespace Application\Frontend\Controllers;

use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;

class SessionsController extends ControllerBase {
	function createAction() {
		if ($this->session->get('user_id')) {
			return $this->response->redirect('/home');
		}
		$next_url = $this->request->getQuery('next');
		if ($this->request->isPost()) {
			$mobile_phone = $this->request->getPost('mobile_phone', 'string');
			$password     = $this->request->getPost('password', 'string');
			$errors       = [];
			if (!$this->security->checkToken()) {
				$errors[] = 'Token form tidak valid';
			}
			if (!$mobile_phone) {
				$errors[] = 'Nomor HP harus diisi';
			}
			if (!$password) {
				$errors[] = 'Password harus diisi';
			}
			if (!$errors) {
				$user = User::findFirst(['status = 1 AND premium_merchant = 1 AND mobile_phone = ?0 AND role_id = ?1', 'bind' => [
					$mobile_phone,
					Role::MERCHANT,
				]]);
				if ($user && $this->security->checkHash($password, $user->password)) {
					$login_history          = new LoginHistory;
					$login_history->user_id = $user->id;
					$login_history->create();
					$this->session->set('user_id', $user->id);
					return $this->response->redirect($next_url ?: '/home');
				}
				$errors[] = 'Email dan/atau password salah';
			}
			foreach ($errors as $error) {
				$this->flashSession->error($error);
			}
			$this->view->email = $mobile_phone;
		}
		$this->view->token_key = $this->security->getTokenKey();
		$this->view->token     = $this->security->getToken();
		$this->view->next_url  = $next_url;
	}

	function deleteAction() {
		if ($this->session->get('user_id')) {
			$this->session->remove('user_id');
			$this->flashSession->success('Anda sudah logout dari IP: ' . $this->request->getClientAddress());
		}
		return $this->response->redirect('/sessions/create');
	}
}