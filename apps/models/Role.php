<?php

namespace Application\Models;

use Application\Models\BaseModel;

class Role extends BaseModel {
	const ANONYMOUS   = 0;
	const SUPER_ADMIN = 1;
	const ADMIN       = 2;
	const MERCHANT    = 3;

	public $id;
	public $name;
	public $description;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'roles';
	}

	function initialize() {
		parent::initialize();
		$this->hasMany('id', 'Application\Models\User', 'role_id', ['alias'  => 'users']);
	}
}