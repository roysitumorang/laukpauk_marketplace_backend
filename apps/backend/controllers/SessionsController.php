<?php

namespace Application\Backend\Controllers;

use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Db;

class SessionsController extends ControllerBase {
	function index() {
		$this->dispatcher->forward([
			'controller' => 'sessions',
			'action'     => 'create',
		]);
	}

	function createAction() {
		if ($this->session->get('user_id')) {
			return $this->response->redirect('/admin/home');
		}
		$next_url = $this->request->getQuery('next');
		if ($this->request->isPost()) {
			$email    = $this->request->getPost('email', 'string');
			$password = $this->request->getPost('password', 'string');
			$errors   = [];
			if (!$this->security->checkToken()) {
				$errors[] = 'Token form tidak valid';
			}
			if (!$email) {
				$errors[] = 'Email harus diisi';
			}
			if (!$password) {
				$errors[] = 'Password harus diisi';
			}
			if (!$errors) {
				$user = User::findFirst(['status = 1 AND email = :email: AND role_id IN ({role_ids:array})', 'bind' => [
					'email'    => $email,
					'role_ids' => [Role::SUPER_ADMIN, Role::ADMIN],
				]]);
				if ($user && $this->security->checkHash($password, $user->password)) {
					$login_history          = new LoginHistory;
					$login_history->user_id = $user->id;
					$login_history->create();
					$this->session->set('user_id', $user->id);
					return $this->response->redirect($next_url ?: '/admin/home');
				}
				$errors[] = 'Email dan/atau password salah';
			}
			foreach ($errors as $error) {
				$this->flashSession->error($error);
			}
			$this->view->email = $email;
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
		return $this->response->redirect('/admin/sessions/create');
	}
}