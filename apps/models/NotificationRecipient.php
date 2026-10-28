<?php

namespace Application\Models;

use Phalcon\Mvc\Model;

#[\AllowDynamicProperties]
class NotificationRecipient extends Model {
	public $notification_id;
	public $user_id;
	public $read_at;

	function initialize() {
		$this->setSource('notification_recipient');
		$this->belongsTo('notification_id', Notification::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', User::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}

	function read() {
		return $this->assign(['read_at' => $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s')])->update();
	}
}