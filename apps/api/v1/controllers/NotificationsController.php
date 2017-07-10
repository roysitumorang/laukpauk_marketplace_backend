<?php

namespace Application\Api\V1\Controllers;

use Application\Models\NotificationRecipient;

class NotificationsController extends ControllerBase {
	function indexAction() {
		$notifications = [];
		$resultset     = $this->_current_user->getRelated('notifications', [
			'Application\Models\NotificationRecipient.read_at IS NULL',
			'order' => 'Application\Models\Notification.id DESC',
		]);
		foreach ($resultset as $notification) {
			$notifications[$notification->id] = $notification->subject;
		}
		$this->_response['status']                = 1;
		$this->_response['data']['notifications'] = $notifications;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function showAction($id) {
		$notification = $this->_current_user->getRelated('notifications', [
			'Application\Models\NotificationRecipient.read_at IS NULL AND Application\Models\Notification.id = ?0',
			'bind' => [$id]
		])->getFirst();
		if ($notification) {
			$notification_recipient                  = NotificationRecipient::findFirst([
				'user_id = :user_id: AND notification_id = :notification_id:',
				'bind' => [
					'user_id'         => $this->_current_user->id,
					'notification_id' => $notification->id,
				],
			]);
			$notification_recipient->read();
			$this->_response['status']               = 1;
			$this->_response['data']['notification'] = [
				'id'      => $notification->id,
				'subject' => $notification->subject,
				'link'    => $notification->old_mobile_target_url,
			];
		} else {
			$this->_response['message'] = 'Notifikasi tidak ditemukan!';
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
