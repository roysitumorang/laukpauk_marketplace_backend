<?php

namespace Application\Backend\Controllers;

use Application\Models\NotificationRecipient;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class NotificationsController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new PaginatorModel([
			'data'  => $this->currentUser->getRelated('notifications', [
				'columns' => 'Application\Models\Notification.id, Application\Models\Notification.subject, Application\Models\Notification.link, Application\Models\Notification.created_at, Application\Models\NotificationRecipient.read_at',
				'order'   => 'Application\Models\Notification.id DESC',
			]),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page          = $paginator->getPaginate();
		$pages         = $this->_setPaginationRange($page);
		$notifications = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$notifications[] = $item;
		}
		$this->view->menu          = $this->_menu('Options');
		$this->view->notifications = $notifications;
		$this->view->pages         = $pages;
		$this->view->page          = $paginator->getPaginate();
	}

	function updateAction($id) {
		if ($this->dispatcher->getParam('read')) {
			$notification_recipient = NotificationRecipient::findFirst([
				'conditions' => 'user_id = ?0 AND notification_id = ?1 AND read_at IS NULL',
				'bind'       => [
					$this->currentUser->id,
					$id,
				],
			]);
			if ($notification_recipient) {
				$notification_recipient->update(['read_at' => $this->currentDatetime->format('Y-m-d H:i:s')]);
			}
		}
	}
}