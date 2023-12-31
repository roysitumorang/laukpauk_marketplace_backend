<?php

namespace Application\Api\V3\Merchant;

use Application\Models\NotificationRecipient;

class NotificationsController extends ControllerBase {
	function indexAction() {
		$notifications = [];
		$result        = $this->currentUser->getRelated('notifications', [
			'Application\Models\NotificationRecipient.read_at IS NULL',
			'order'   => 'id DESC',
			'columns' => 'id, title',
		]);
		foreach ($result as $notification) {
			$notifications[] = $notification;
		}
		$this->_response['status']                = 1;
		$this->_response['data']['notifications'] = $notifications;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function showAction($id) {
		try {
			$notification = $this->currentUser->getRelated('notifications', [
				'id = ?0',
				'bind' => [$id]
			])->getFirst();
			if (!$notification) {
				throw new Exception('Notifikasi tidak ditemukan!');
			}
			$notification_recipient = NotificationRecipient::findFirst([
				'user_id = ?0 AND notification_id = ?1 AND read_at IS NULL',
				'bind' => [
					$this->currentUser->id,
					$notification->id,
				],
			]);
			if ($notification_recipient) {
				$notification_recipient->read();
			}
			$this->_response['status']               = 1;
			$this->_response['data']['notification'] = [
				'id'      => $notification->id,
				'title'   => $notification->title,
				'message' => $notification->message,
			];
			if ($notification->new_mobile_target_url != 'tab.notification') {
				$this->_response['data']['notification']['target_url']        = $notification->new_mobile_target_url;
				$this->_response['data']['notification']['target_parameters'] = $notification->new_mobile_target_parameters;
			}
			$this->_response['data']['total_new_notifications'] = $this->currentUser->totalNewNotifications();
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}
