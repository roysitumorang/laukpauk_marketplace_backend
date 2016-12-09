<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class AccessToken extends ModelBase {
	public $id;
	public $user_id;
	public $user_agent;
	public $ip_address;
	public $expired_at;
	public $created_at;
	public $updated_at;

	const LIFETIME = '14 days';

	function getSource() {
		return 'access_tokens';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'      => 'user',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => true,
			],
		]);
	}

	function beforeValidationOnCreate() {
		$request          = $this->getDI()->getRequest();
		$this->id         = bin2hex(random_bytes(16));
		$this->user_agent = $request->getUserAgent();
		$this->ip_address = $request->getClientAddress();
		$this->expired_at = $this->getDI()->getCurrentDatetime()->modify(static::LIFETIME)->format('Y-m-d H:i:s');
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['user_agent', 'ip_address', 'expired_at'], new PresenceOf([
			'message' => [
				'user_agent' => 'user agent harus diisi',
				'ip_address' => 'ip address harus diisi',
				'expired_at' => 'waktu expired harus diisi',
			],
		]));
		return $this->validate($validator);
	}
}