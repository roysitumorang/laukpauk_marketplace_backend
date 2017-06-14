<?php

namespace Application\Api\V3\Controllers;

use Application\Models\NotificationRecipient;

class NotificationsController extends ControllerBase {
	function indexAction() {
		$notifications = [];
		$resultset     = $this->_current_user->getRelated('notifications', [
			"Application\Models\Notification.type = 'mobile' AND Application\Models\NotificationRecipient.read_at IS NULL",
			'order' => 'Application\Models\Notification.id DESC',
		]);
		foreach ($resultset as $notification) {
			$notifications[] = ['id' => $notification->id, 'title' => $notification->title];
		}
		$this->_response['status']                = 1;
		$this->_response['data']['notifications'] = $notifications;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function showAction($id) {
		$notification = $this->_current_user->getRelated('notifications', [
			"Application\Models\Notification.type = 'mobile' AND Application\Models\NotificationRecipient.read_at IS NULL AND Application\Models\Notification.id = ?0",
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
				'subject'    => $notification->subject,
				'target_url' => $notification->target_url,
			];
		} else {
			$this->_response['message'] = 'Notifikasi tidak ditemukan!';
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
