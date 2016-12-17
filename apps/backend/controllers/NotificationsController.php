<?php

namespace Application\Backend\Controllers;

use Application\Models\NotificationRecipient;

class NotificationsController extends ControllerBase {
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