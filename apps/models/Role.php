<?php

namespace Application\Models;

use Application\Models\ModelBase;

class Role extends ModelBase {
	const ANONYMOUS   = 0;
	const SUPER_ADMIN = 1;
	const ADMIN       = 2;
	const MERCHANT    = 3;
	const BUYER       = 4;

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
		$this->hasManyToMany('id', 'Application\Models\UserRole', 'role_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'users']);
	}
}