<?php

namespace Application\Backend\Controllers;

use Application\Models\{Role, Sms, User};
use Phalcon\Paginator\Adapter\Model;

class SmsController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Mailbox');
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => $this->currentUser->getRelated('ownSms', ['order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page  = $paginator->paginate();
		$pages = $this->_setPaginationRange($page);
		$texts = [];
		foreach ($page->items as $item) {
			$recipients = $item->countRecipients() > 1 ? 'Semua Member' : $item->getRelated('recipients')->getFirst()->name;
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('recipients', $recipients);
			$texts[] = $item;
		}
		$this->view->texts = $texts;
		$this->view->pages = $pages;
		$this->view->page  = $page;
	}

	function createAction() {
		$sms        = new Sms;
		$roles      = [];
		$recipients = [];
		$role_id    = '';
		$user_id    = '';
		$condition  = 'status = 1';
		foreach (Role::find(["name IN('Merchant', 'Buyer')", 'column' => 'id, name', 'order' => 'LOWER(name) DESC']) as $item) {
			$roles[] = $item;
		}
		if ($this->request->isPost()) {
			$role_id = $this->request->getPost('role_id', 'int');
			$user_id = $this->request->getPost('user_id');
			if ($role_id && $role = Role::findFirst("name IN('Merchant', 'Buyer') AND id = {$role_id}")) {
				$condition .= " AND role_id = {$role->id}";
			}
			if ($user_id && $user = User::findFirst("status = 1 AND id = {$user_id}")) {
				$condition .= " AND id = {$user->id}";
			}
			foreach (User::find([$condition, 'order' => 'LOWER(name)']) as $item) {
				$recipients[] = $item;
			}
			if (!$recipients) {
				$this->flashSession->error('Penerima notifikasi belum ada.');
			} else {
				$sms->setBody($this->request->getPost('body'));
				$sms->user_id = $this->currentUser->id;
				if ($sms->validation() && $sms->send($recipients)) {
					$this->flashSession->success('SMS berhasil dikirim.');
					return $this->response->redirect('/admin/sms');
				}
				$this->flashSession->error('SMS tidak terkirim, silahkan cek form dan coba lagi.');
				foreach ($sms->getMessages() as $error) {
					$this->flashSession->error($error);
				}
			}
		} else {
			foreach (User::find([$condition, 'order' => 'LOWER(name)']) as $item) {
				$recipients[] = $item;
			}
		}
		$this->view->sms     = $sms;
		$this->view->roles   = $roles;
		$this->view->users   = $recipients;
		$this->view->role_id = $role_id;
		$this->view->user_id = $user_id;
	}

	function recipientsAction() {
		$condition  = 'status = 1';
		$recipients = [];
		$role_id    = $this->dispatcher->getParam('role_id', 'int');
		if ($role_id && $role = Role::findFirst("name IN('Merchant', 'Buyer') AND id = {$role_id}")) {
			$condition .= " AND role_id = {$role->id}";
		}
		$result = User::find([$condition, 'columns' => 'id, COALESCE(company, name) AS name, mobile_phone', 'order' => 'LOWER(name)']);
		foreach ($result as $item) {
			$recipients[] = $item;
		}
		$this->response->setContentType('application/json', 'UTF-8');
		$this->response->setContent(json_encode($recipients));
		$this->response->send();
		exit;
	}
}