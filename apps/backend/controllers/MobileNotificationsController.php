<?php

namespace Application\Backend\Controllers;

use Application\Models\Notification;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Paginator\Adapter\Model;

class MobileNotificationsController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Mailbox');
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => $this->currentUser->getRelated('ownNotifications', ["type = 'mobile'", 'order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page          = $paginator->getPaginate();
		$pages         = $this->_setPaginationRange($page);
		$notifications = [];
		foreach ($page->items as $item) {
			$recipients = $item->countRecipients() > 1 ? 'Semua Member' : $item->getRelated('recipients')->getFirst()->name;
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('recipients', $recipients);
			$notifications[] = $item;
		}
		$this->view->notifications = $notifications;
		$this->view->pages         = $pages;
		$this->view->page          = $paginator->getPaginate();
	}

	function createAction() {
		$notification = new Notification;
		$merchants    = [];
		$roles        = [];
		$recipients   = [];
		$merchant_id  = '';
		$role_id      = '';
		$user_id      = '';
		$condition    = 'status = 1 AND EXISTS(SELECT 1 FROM Application\Models\Device WHERE Application\Models\Device.user_id = Application\Models\User.id)';
		foreach (User::find(['status = 1 AND premium_merchant = 1', 'column' => 'id, name, mobile_phone', 'order' => 'LOWER(company)']) as $item) {
			$merchants[] = $item;
		}
		foreach (Role::find(["name IN('Merchant', 'Buyer')", 'column' => 'id, name', 'order' => 'LOWER(name) DESC']) as $item) {
			$roles[] = $item;
		}
		if ($this->request->isPost()) {
			$device_tokens = [];
			$content       = [
				'title'   => $this->request->getPost('title'),
				'message' => $this->request->getPost('message'),
			];
			$result      = User::query()->where('status = 1')->join('Application\Models\Device', 'Application\Models\User.id = b.user_id', 'b')->columns(['token']);
			$merchant_id = $this->request->getPost('merchant_id', 'int');
			$role_id     = $this->request->getPost('role_id', 'int');
			$user_id     = $this->request->getPost('user_id');
			if ($merchant_id && $merchant = User::findFirst("status = 1 AND premium_merchant = 1 AND id = {$merchant_id}")) {
				$condition .= " AND (id = {$merchant->id} OR merchant_id = {$merchant->id})";
				$result->andWhere("Application\Models\User.id = {$merchant->id} OR merchant_id = {$merchant->id}");
			}
			if ($role_id && $role = Role::findFirst("name IN('Merchant', 'Buyer') AND id = {$role_id}")) {
				$condition .= " AND role_id = {$role->id}";
				$result->andWhere("role_id = {$role->id}");
			}
			if ($user_id && $user = User::findFirst("status = 1 AND id = {$user_id}")) {
				$condition .= " AND id = {$user->id}";
				$result->andWhere("Application\Models\User.id = {$user->id}");
			}
			foreach ($result->execute() as $row) {
				$device_tokens[] = $row->token;
			}
			if (!$device_tokens) {
				$this->flashSession->error('Penerima notifikasi belum ada.');
			} else {
				foreach (User::find($condition) as $user) {
					$recipients[] = $user;
				}
				$notification->setType('mobile');
				$notification->setTitle($this->request->getPost('title'));
				$notification->setMessage($this->request->getPost('message'));
				$notification->user_id    = $this->currentUser->id;
				$notification->recipients = $recipients;
				if ($notification->push($device_tokens, $content)) {
					$this->flashSession->success('Notifikasi berhasil dikirim.');
					return $this->response->redirect('/admin/mobile_notifications');
				}
				$this->flashSession->error('Notifikasi tidak terkirim, silahkan cek form dan coba lagi.');
				foreach ($notification->getMessages() as $error) {
					$this->flashSession->error($error);
				}
			}
		} else {
			foreach (User::find([$condition . ' AND premium_merchant IS NULL AND merchant_id IS NULL', 'columns' => 'id, COALESCE(company, name) AS name, mobile_phone', 'order' => 'LOWER(name)']) as $user) {
				$recipients[] = $user;
			}
		}
		$this->view->notification = $notification;
		$this->view->merchants    = $merchants;
		$this->view->roles        = $roles;
		$this->view->users        = $recipients;
		$this->view->merchant_id  = $merchant_id;
		$this->view->role_id      = $role_id;
		$this->view->user_id      = $user_id;
	}

	function recipientsAction() {
		$condition   = 'status = 1 AND EXISTS(SELECT 1 FROM Application\Models\Device WHERE Application\Models\Device.user_id = Application\Models\User.id)';
		$recipients  = [];
		$merchant_id = $this->dispatcher->getParam('merchant_id', 'int');
		$role_id     = $this->dispatcher->getParam('role_id', 'int');
		if ($merchant_id && $merchant = User::findFirst("status = 1 AND premium_merchant = 1 AND id = {$merchant_id}")) {
			$condition .= " AND (id = {$merchant->id} OR merchant_id = {$merchant->id})";
		}
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