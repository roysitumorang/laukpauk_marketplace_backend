<?php

namespace Application\Api\V3\Merchant;

use Application\Models\NotificationRecipient;

class NotificationsController extends ControllerBase {
	function indexAction() {
		$notifications = [];
		$result        = $this->_current_user->getRelated('notifications', [
			'Application\Models\NotificationRecipient.read_at IS NULL',
			'order' => 'Application\Models\Notification.id DESC',
		]);
		foreach ($result as $notification) {
			$notifications[] = ['id' => $notification->id, 'title' => $notification->title];
		}
		$this->_response['status']                          = 1;
		$this->_response['data']['notifications']           = $notifications;
		$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function showAction($id) {
		$notification = $this->_current_user->getRelated('notifications', [
			'Application\Models\NotificationRecipient.read_at IS NULL AND Application\Models\Notification.id = ?0',
			'bind' => [$id]
		])->getFirst();
		if ($notification) {
			$notification_recipient = NotificationRecipient::findFirst([
				'user_id = ?0 AND notification_id = ?1',
				'bind' => [
					$this->_current_user->id,
					$notification->id,
				],
			]);
			$notification_recipient->read();
			$this->_response['status']               = 1;
			$this->_response['data']['notification'] = [
				'id'         => $notification->id,
				'title'      => $notification->title,
				'message'    => $notification->message,
				'target_url' => $notification->target_url,
			];
			$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		} else {
			$this->_response['message'] = 'Notifikasi tidak ditemukan!';
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
