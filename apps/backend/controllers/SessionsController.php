<?php

namespace Application\Backend\Controllers;
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
	}

	function createAction() {
		if ($this->request->isPost()) {
			$username = $this->request->getPost('username');
			$password = $this->request->getPost('password');
			$user = User::findFirst([
				"(email = :username: OR username = :username:) AND user_type = 'admin' AND status = 'active'",
				'bind' => ['username' => $username],
			]);
			if ($user && $this->security->checkHash($password, $user->password)) {
				$this->session->set('user_id', $user->id);
				$this->flash->success('Welcome ' . $user->name);
				return $this->response->redirect('/admin/home');
			}
			$this->flash->error('Username dan/atau password salah');
		}
		return $this->dispatcher->forward([
			'controller' => 'sessions',
			'action'     => 'new',
		]);
	}

	function deleteAction() {
		if ($this->session->get('user_id')) {
			$this->session->destroy();
		}
		return $this->response->redirect('/admin/home');
	}
}
