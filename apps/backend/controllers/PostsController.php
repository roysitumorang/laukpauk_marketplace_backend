<?php

namespace Application\Backend\Controllers;

use Application\Models\Role;
use Application\Models\User;

class PostsController extends ControllerBase {
	function indexAction() {
		$user_id = $this->request->getPost('user_id', 'int') ?: $this->dispatcher->getParam('user_id', 'int');
		$users   = [];
		$result = User::find(['status = 1 AND (role_id = ?0 OR (role_id = ?1 AND premium_merchant = 1))', 'bind' => [Role::SUPER_ADMIN, Role::MERCHANT], 'order' => 'role_id, company']);
		foreach ($result as $user) {
			$users[] = $user;
		}
		if (!$user_id || !($user = User::findFirst(['status = 1 AND (role_id = ?0 OR (role_id = ?1 AND premium_merchant = 1)) AND id = ?2', 'bind' => [Role::SUPER_ADMIN, Role::MERCHANT, $user_id]]))) {
			$user = $users[0];
		}
		$this->view->menu  = $this->_menu('Content');
		$this->view->users = $users;
		$this->view->user  = $user;
	}

	function saveAction() {
		if ($this->request->isPost() &&
			($user_id = $this->request->getPost('user_id', 'int')) &&
			($user = User::findFirst(['status = 1 AND (role_id = ?0 OR (role_id = ?1 AND premium_merchant = 1)) AND id = ?2', 'bind' => [Role::SUPER_ADMIN, Role::MERCHANT, $user_id]]))) {
			$user->setCompanyProfile($this->request->getPost('company_profile'));
			$user->setTermsConditions($this->request->getPost('terms_conditions'));
			$user->setContact($this->request->getPost('contact'));
			if ($user->validation() && $user->update()) {
				$this->flashSession->success('Update konten berhasil.');
				return $this->response->redirect("/admin/posts/index/user_id:{$user->id}");
			}
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		return $this->dispatcher->forward(['action' => 'index']);
	}
}