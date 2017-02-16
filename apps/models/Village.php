<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Village extends ModelBase {
	public $id;
	public $subdistrict_id;
	public $name;
	public $zip_code;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'villages';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('subdistrict_id', 'Application\Models\Subdistrict', 'id', [
			'alias'    => 'subdistrict',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\User', 'village_id', ['alias' => 'users']);
		$this->hasManyToMany('id', 'Application\Models\ServiceArea', 'village_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'merchants']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		return $this->validate($validator);
	}
}