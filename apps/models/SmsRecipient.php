<?php

namespace Application\Models;

class SmsRecipient extends ModelBase {
	public $sms_id;
	public $user_id;
	public $mobile_phone;
	public $status;

	function getSource() {
		return 'sms_recipient';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('sms_id', Sms::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', User::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}