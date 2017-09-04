<?php

namespace Application\Backend\Controllers;

use Application\Models\Notification;
use Application\Models\Role;
use Application\Models\User;
use DateTime;
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
		$this->view->page          = $paginator->getPaginate();
	}

	function createAction() {
		$notification = new Notification;
		$roles        = [];
		$recipients   = [];
		$role_id      = '';
		$user_id      = '';
		$condition    = 'status = 1 AND EXISTS(SELECT 1 FROM Application\Models\Device WHERE Application\Models\Device.user_id = Application\Models\User.id)';
		foreach (Role::find(["name IN('Merchant', 'Buyer')", 'column' => 'id, name', 'order' => 'LOWER(name) DESC']) as $item) {
			$roles[] = $item;
		}
		if ($this->request->isPost()) {
			$result  = User::query()->where('status = 1')->join('Application\Models\Device', 'Application\Models\User.id = b.user_id', 'b')->columns(['token']);
			$role_id = $this->request->getPost('role_id', 'int');
			$user_id = $this->request->getPost('user_id');
			if ($role_id && $role = Role::findFirst("name IN('Merchant', 'Buyer') AND id = {$role_id}")) {
				$condition .= " AND role_id = {$role->id}";
				$result->andWhere("role_id = {$role->id}");
			}
			if ($user_id && $user = User::findFirst("status = 1 AND id = {$user_id}")) {
				$condition .= " AND id = {$user->id}";
				$result->andWhere("Application\Models\User.id = {$user->id}");
			}
			foreach (User::find($condition) as $user) {
				$recipients[] = $user;
			}
			if (!$recipients) {
				$this->flashSession->error('Penerima notifikasi belum ada.');
			} else {
				$notification->setTitle($this->request->getPost('title'));
				$notification->setMessage($this->request->getPost('message'));
				$notification->setAdminTargetUrl('/admin/notifications/');
				$notification->setMerchantTargetUrl('/notifications/');
				$notification->setOldMobileTargetUrl('#/tabs/notification/');
				$notification->setNewMobileTargetUrl('tab.notification');
				$notification->user_id = $this->currentUser->id;
				if ($notification->push($recipients)) {
					$notification->setAdminTargetUrl($notification->admin_target_url . $notification->id);
					$notification->setMerchantTargetUrl($notification->merchant_target_url . $notification->id);
					$notification->setOldMobileTargetUrl($notification->old_mobile_target_url . $notification->id);
					$notification->update();
					$this->flashSession->success('Notifikasi berhasil dikirim.');
					return $this->response->redirect('/admin/push_notifications');
				}
				$this->flashSession->error('Notifikasi tidak terkirim, silahkan cek form dan coba lagi.');
				foreach ($notification->getMessages() as $error) {
					$this->flashSession->error($error);
				}
			}
		} else {
			foreach (User::find([$condition, 'columns' => 'id, COALESCE(company, name) AS name, mobile_phone', 'order' => 'LOWER(name)']) as $user) {
				$recipients[] = $user;
			}
		}
		$this->view->notification = $notification;
		$this->view->roles        = $roles;
		$this->view->users        = $recipients;
		$this->view->role_id      = $role_id;
		$this->view->user_id      = $user_id;
	}

	function recipientsAction() {
		$condition  = 'status = 1 AND EXISTS(SELECT 1 FROM Application\Models\Device WHERE Application\Models\Device.user_id = Application\Models\User.id)';
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