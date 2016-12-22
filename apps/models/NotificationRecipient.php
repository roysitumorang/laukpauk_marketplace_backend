<?php

namespace Application\Models;

use Application\Models\ModelBase;

class NotificationRecipient extends ModelBase {
	public $id;
	public $notification_id;
	public $user_id;
	public $read_at;

	function getSource() {
		return 'notification_recipients';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('notification_id', 'Application\Models\Notification', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}

	function read() {
		return $this->update(['read_at' => $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s')]);
	}
}