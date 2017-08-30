<?php

namespace Application\Api\V3\Buyer;

use Application\Models\NotificationRecipient;
use Exception;

class NotificationsController extends ControllerBase {
	function indexAction() {
		$notifications = [];
		$result        = $this->_current_user->getRelated('notifications', [
			'Application\Models\NotificationRecipient.read_at IS NULL',
			'order' => 'Application\Models\Notification.id DESC',
		]);
		foreach ($result as $notification) {
			$notifications[] = ['id' => $notification->id, 'title' => $notification->title, 'target_url' => $notification->new_mobile_target_url, 'target_parameters' => $notification->new_mobile_target_parameters];
		}
		$this->_response['status']                          = 1;
		$this->_response['data']['notifications']           = $notifications;
		$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function showAction($id) {
		try {
			$notification = $this->_current_user->getRelated('notifications', [
				'Application\Models\NotificationRecipient.read_at IS NULL AND Application\Models\Notification.id = ?0',
				'bind' => [$id]
			])->getFirst();
			if (!$notification) {
				throw new Exception('Notifikasi tidak ditemukan!');
			}
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
				'id'                => $notification->id,
				'title'             => $notification->title,
				'message'           => $notification->message,
				'target_url'        => $notification->new_mobile_target_url,
				'target_parameters' => $notification->new_mobile_target_parameters,
			];
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}
