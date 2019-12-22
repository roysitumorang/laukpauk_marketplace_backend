<?php

namespace Application\Models;

class MessageRecipient extends ModelBase {
	public $id;
	public $message_id;
	public $user_id;
	public $read_at;

	function initialize() {
		$this->setSource('message_recipients');
		parent::initialize();
		$this->belongsTo('message_id', Message::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', User::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}