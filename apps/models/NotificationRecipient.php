<?php

namespace Application\Models;

use Phalcon\Mvc\Model;

class NotificationRecipient extends Model {
	public $notification_id;
	public $user_id;
	public $read_at;

	function getSource() {
		return 'notification_recipient';
	}

	function initialize() {
		$this->belongsTo('notification_id', Notification::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', User::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}

	function read() {
		return $this->update(['read_at' => $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s')]);
	}
}