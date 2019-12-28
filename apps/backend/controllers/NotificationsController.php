<?php

namespace Application\Backend\Controllers;

use Application\Models\{Notification, NotificationRecipient};
use DateTime;
use IntlDateFormatter;
use Phalcon\Paginator\Adapter\QueryBuilder;

class NotificationsController extends ControllerBase {
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
		$builder      = $this->modelsManager->createBuilder()
				->columns([
					'a.id',
					'a.title',
					'a.admin_target_url',
					'a.created_at',
					'b.read_at',
				])
				->addFrom(Notification::class, 'a')
				->join(NotificationRecipient::class, 'a.[id] = b.[notification_id]', 'b')
				->where('b.user_id = :user_id:', ['user_id' => $this->currentUser->id])
				->orderBy('a.id DESC');
		$paginator    = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page          = $paginator->paginate();
		$pages         = $this->_setPaginationRange($page);
		$notifications = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('created_at', $datetime_formatter->format(new DateTime($item->created_at, $this->currentDatetime->getTimezone())));
			$notifications[] = $item;
		}
		$this->view->menu          = $this->_menu('Options');
		$this->view->notifications = $notifications;
		$this->view->pages         = $pages;
		$this->view->page          = $page;
	}

	function readAction($id) {
		if ($this->request->isPost()) {
			$notification_recipient = NotificationRecipient::findFirst([
				'conditions' => 'user_id = ?0 AND notification_id = ?1 AND read_at IS NULL',
				'bind'       => [$this->currentUser->id, $id],
			]);
			if ($notification_recipient) {
				$notification_recipient->update(['read_at' => $this->currentDatetime->format('Y-m-d H:i:s')]);
			}
		}
	}
}