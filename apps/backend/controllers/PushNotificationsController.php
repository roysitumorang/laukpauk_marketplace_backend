<?php

namespace Application\Backend\Controllers;

use Application\Models\Notification;
use Application\Models\Role;
use Application\Models\User;
use DateTime;
use Ds\Vector;
use IntlDateFormatter;
use Phalcon\Paginator\Adapter\Model;

class PushNotificationsController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Mailbox');
	}

	function indexAction() {
		$datetime_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'd MMM yyyy HH.mm'
		);
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => $this->currentUser->getRelated('ownNotifications', ['order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page          = $paginator->getPaginate();
		$pages         = $this->_setPaginationRange($page);
		$notifications = [];
		foreach ($page->items as $item) {
			$recipients = $item->countRecipients() > 1 ? 'Semua Member' : $item->getRelated('recipients')->getFirst()->name;
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('created_at', $datetime_formatter->format(new DateTime($item->created_at, $this->currentDatetime->getTimezone())));
			$item->writeAttribute('recipients', $recipients);
			$notifications[] = $item;
		}
		$this->view->notifications = $notifications;
		$this->view->pages         = $pages;
		$this->view->page          = $page;
	}

	function createAction() {
		$notification = new Notification;
		$roles        = Role::find(["name IN('Merchant', 'Buyer')", 'column' => 'id, name', 'order' => 'LOWER(name) DESC']);
		$recipients   = new Vector;
		$role_id      = '';
		$user_id      = '';
		$condition    = 'status = 1 AND (device_token IS NOT NULL OR EXISTS(SELECT 1 FROM Application\Models\Device WHERE Application\Models\Device.user_id = Application\Models\User.id))';
		if ($this->request->isPost()) {
			if ($role_id = $this->request->getPost('role_id', 'int!')) {
				$condition .= " AND role_id = {$role_id}";
			}
			if ($user_id = $this->request->getPost('user_id', 'int!')) {
				$condition .= " AND id = {$user_id}";
			}
			foreach (User::find($condition) as $user) {
				$recipients->push($user);
			}
			if (!$recipients) {
				$this->flashSession->error('Penerima notifikasi belum ada.');
			} else {
				$notification->assign($this->request->getPost(), null, [
					'title',
					'message',
				])->assign([
					'admin_target_url'      => '/admin/notifications/',
					'merchant_target_url'   => '/notifications/',
					'old_mobile_target_url' => '#/tabs/notification/',
					'new_mobile_target_url' => 'tab.notification',
					'user_id'               => $this->currentUser->id,
				])->setNewImage($this->request->getUploadedFiles()[0]);
				if ($notification->push($recipients->toArray())) {
					$notification->update([
						'admin_target_url'      => $notification->admin_target_url . $notification->id,
						'merchant_target_url'   => $notification->merchant_target_url . $notification->id,
						'old_mobile_target_url' => $notification->old_mobile_target_url . $notification->id,
					]);
					$this->flashSession->success('Notifikasi berhasil dikirim.');
					return $this->response->redirect('/admin/push_notifications');
				}
				$this->flashSession->error('Notifikasi tidak terkirim, silahkan cek form dan coba lagi.');
				foreach ($notification->getMessages() as $error) {
					$this->flashSession->error($error);
				}
			}
		}
		foreach (User::find([$condition, 'columns' => 'id, COALESCE(company, name) AS name, mobile_phone', 'order' => 'LOWER(name)']) as $user) {
			$recipients->push([
				'id'    => $user->id,
				'label' => $user->mobile_phone . ' / ' . $user->name,
			]);
		}
		$this->view->setVars([
			'notification' => $notification,
			'roles'        => $roles,
			'recipients'   => $recipients,
			'role_id'      => $role_id,
			'user_id'      => $user_id,
		]);
	}

	function recipientsAction() {
		$condition  = 'status = 1 AND (device_token IS NOT NULL OR EXISTS(SELECT 1 FROM Application\Models\Device WHERE Application\Models\Device.user_id = Application\Models\User.id))';
		$recipients = [];
		if ($role_id = $this->dispatcher->getParam('role_id', 'int!')) {
			$condition .= " AND role_id = {$role_id}";
		}
		foreach (User::find([$condition, 'columns' => 'id, COALESCE(company, name) AS name, mobile_phone', 'order' => 'LOWER(name)']) as $user) {
			$recipients[] = [
				'id'    => $user->id,
				'label' => $user->mobile_phone . ' / ' . $user->name,
			];
		}
		$this->response->setContentType('application/json', 'UTF-8')
				->setContent(json_encode($recipients))
				->send();
		exit;
	}
}