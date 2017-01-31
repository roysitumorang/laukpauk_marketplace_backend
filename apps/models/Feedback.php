<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Feedback extends ModelBase {
	public $id;
	public $content;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'feedbacks';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('created_by', 'Application\Models\User', 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('content', new PresenceOf([
			'message' => 'feedback harus diisi',
		]));
		return $this->validate($validator);
	}
}
