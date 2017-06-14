<?php

namespace Application\Backend\Controllers;

use Application\Models\Notification;
use Application\Models\User;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class MobileNotificationsController extends ControllerBase {
	function initialize() {
		parent::initialize();
		$this->view->menu  = $this->_menu('Mailbox');
		$this->view->users = User::find(['premium_merchant IS NULL AND merchant_id IS NULL AND status = 1', 'order' => 'name ASC']);
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'id',
				'title',
				'message',
				'target_url',
				'created_at',
			])
			->from('Application\Models\Notification')
			->orderBy('id DESC')
			->where("type = 'mobile' AND user_id = {$this->currentUser->id}");
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page          = $paginator->getPaginate();
		$pages         = $this->_setPaginationRange($page);
		$notifications = [];
		foreach ($page->items as $item) {
			$recipients = '';
			if ($this->db->fetchColumn("SELECT COUNT(1) FROM notification_recipient WHERE notification_id = {$item->id}") > 1) {
				$recipients .= 'Semua Member';
			} else {
				$i      = 0;
				$result = $this->db->query("SELECT a.name FROM users a JOIN notification_recipient b ON a.id = b.user_id WHERE b.notification_id = {$item->id}");
				$result->setFetchMode(Db::FETCH_OBJ);
				while ($row = $result->fetch()) {
					$recipients .= ($i++ ? ', ' : '') . ' ' . $row->name;
				}
			}
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
		$user_id      = '';
		if ($this->request->isPost()) {
			$device_tokens = [];
			$content       = [
				'title'   => $this->request->getPost('title'),
				'message' => $this->request->getPost('message'),
			];
			$notification->user_id = $this->getDI()->getCurrentUser()->id;
			$notification->setType('mobile');
			$notification->setTitle($this->request->getPost('title'));
			$notification->setMessage($this->request->getPost('message'));
			$notification->setTargetUrl('#/tabs/home');
			$user_id = $this->request->getPost('user_id');
			$db      = $this->getDI()->getDb();
			if ($user_id && $recipient = User::findFirst(['id = ?0 AND premium_merchant IS NULL AND merchant_id IS NULL AND status = 1', 'bind' => [$user_id]])) {
				$notification->recipients = [$recipient];
				$result                   = $db->query("SELECT a.token FROM devices a JOIN users b ON a.user_id = b.id WHERE b.premium_merchant IS NULL AND b.merchant_id IS NULL AND b.status = 1 AND b.id = {$recipient->id}");
			} else {
				$notification->recipients = $this->view->users;
				$result                   = $db->query('SELECT a.token FROM devices a JOIN users b ON a.user_id = b.id WHERE b.premium_merchant IS NULL AND b.merchant_id IS NULL AND b.status = 1');
			}
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$device_tokens[] = $row->token;
			}
			if ($notification->validation() && $notification->push($device_tokens, $content)) {
				$this->flashSession->success('Notifikasi berhasil dikirim.');
				return $this->response->redirect('/admin/mobile_notifications');
			}
			$this->flashSession->error('Notifikasi tidak terkirim, silahkan cek form dan coba lagi.');
			foreach ($notification->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->notification = $notification;
		$this->view->user_id      = $user_id;
	}
}