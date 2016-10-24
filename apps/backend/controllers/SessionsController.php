<?php

namespace Application\Backend\Controllers;
use Application\Models\Role;
use Application\Models\User;

class SessionsController extends BaseController {
	function initialize() {
		parent::initialize();
	}

	function newAction() {
		if ($this->session->get('user_id')) {
			$this->response->redirect('/admin/home');
			exit;
		}
		$this->view->token_key = $this->security->getTokenKey();
		$this->view->token     = $this->security->getToken();
	}

	function createAction() {
		if ($this->request->isPost() && $this->security->checkToken()) {
			$email    = $this->request->getPost('email');
			$password = $this->request->getPost('password');
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
				return $this->response->redirect('/admin/home');
			}
			$this->flashSession->error('Email dan/atau password salah');
		}
		return $this->dispatcher->forward([
			'controller' => 'sessions',
			'action'     => 'new',
		]);
	}

	function deleteAction() {
		if ($this->session->get('user_id')) {
			$this->session->remove('user_id');
			$this->flashSession->success('Anda sudah logout dari IP: ' . $this->request->getClientAddress());
		}
		return $this->response->redirect('/admin/sessions/new');
	}
}