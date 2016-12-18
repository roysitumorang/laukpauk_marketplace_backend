<?php

namespace Application\Models;

use Application\Models\ModelBase;

class UserRole extends ModelBase {
	public $id;
	public $user_id;
	public $role_id;
	public $created_by;
	public $created_at;

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('role_id', 'Application\Models\Role', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}