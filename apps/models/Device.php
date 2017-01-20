<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Device extends ModelBase {
	public $id;
	public $user_id;
	public $token;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'devices';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('token', new PresenceOf([
			'message' => 'token harus diisi',
		]));
		$validator->add('token', new Uniqueness([
			'message' => 'token sudah ada',
		]));
		return $this->validate($validator);
	}
}