<?php

namespace Application\Models;

use Application\Models\ModelBase;

class MessageRecipient extends ModelBase {
	public $id;
	public $message_id;
	public $user_id;
	public $read_at;

	function getSource() {
		return 'message_recipients';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('message_id', 'Application\Models\Message', 'id');
		$this->belongsTo('user_id', 'Application\Models\User', 'id');
	}
}