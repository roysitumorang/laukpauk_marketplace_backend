<?php

namespace Application\Models;

use Phalcon\Mvc\Model;

class LoginHistory extends Model {
	public $id;
	public $user_id;
	public $sign_in_at;
	public $ip_address;
	public $user_agent;

	function initialize() {
		$this->belongsTo('user_id', User::class, 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
	}

	function beforeValidationOnCreate() {
		$di = $this->getDI();
		$this->sign_in_at = $di->getCurrentDatetime()->format('Y-m-d H:i:s');
		$this->ip_address = $di->getRequest()->getClientAddress();
		$this->user_agent = $di->getRequest()->getUserAgent();
	}
}