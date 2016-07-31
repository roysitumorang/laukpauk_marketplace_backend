<?php

namespace Application\Models;

use Application\Models\BaseModel;

class Message extends BaseModel {
	public $id;
	public $user_id;
	public $subject;
	public $body;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'messages';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', ['alias' => 'user']);
	}
}
