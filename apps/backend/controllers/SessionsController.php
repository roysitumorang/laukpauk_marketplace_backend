<?php

namespace Application\Backend\Controllers;
use Application\Models\Role;
use Application\Models\User;

class SessionsController extends BaseController {
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
				$user = User::findFirst([
					'email = :email: AND (role_id = :super_admin: OR role_id = :admin:) AND status = :status:',
					'bind' => [
						'email'       => $email,
						'super_admin' => Role::SUPER_ADMIN,
						'admin'       => Role::ADMIN,
						'status'      => User::STATUS_ACTIVE,
					],
				]);
				if ($user && password_verify($password, $user->password)) {
					$this->session->set('user_id', $user->id);
					return $this->response->redirect($next_url ?: '/admin/home');
				}
				$this->flashSession->error('Email dan/atau password salah');
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