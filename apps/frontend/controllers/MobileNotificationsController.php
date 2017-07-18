<?php

namespace Application\Frontend\Controllers;

use Application\Models\Notification;
use Application\Models\User;
use DateTime;
use IntlDateFormatter;
use Phalcon\Paginator\Adapter\Model;

class MobileNotificationsController extends ControllerBase {
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
		$users        = [];
		$recipients   = [];
		$user_id      = '';
		$condition    = "merchant_id = {$this->currentUser->id} AND status = 1 AND EXISTS(SELECT 1 FROM Application\Models\Device WHERE Application\Models\Device.user_id = Application\Models\User.id)";
		foreach (User::find([$condition, 'columns' => 'id, COALESCE(company, name) AS name, mobile_phone', 'order' => 'LOWER(name)']) as $user) {
			$users[] = $user;
		}
		if ($this->request->isPost()) {
			$user_id = $this->request->getPost('user_id');
			if ($user_id) {
				$condition .= " AND id = {$user_id}";
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
					return $this->response->redirect('/mobile_notifications');
				}
				$this->flashSession->error('Notifikasi tidak terkirim, silahkan cek form dan coba lagi.');
				foreach ($notification->getMessages() as $error) {
					$this->flashSession->error($error);
				}
			}
		}
		$this->view->notification = $notification;
		$this->view->users        = $users;
		$this->view->user_id      = $user_id;
	}
}