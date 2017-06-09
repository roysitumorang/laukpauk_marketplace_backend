<?php

namespace Application\Models;

use Application\Models\ModelBase;

class SmsRecipient extends ModelBase {
	public $sms_id;
	public $user_id;

	function getSource() {
		return 'sms_recipients';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('sms_id', 'Application\Models\Sms', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}