<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Village extends ModelBase {
	public $id;
	public $subdistrict_id;
	public $name;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'villages';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('subdistrict_id', Subdistrict::class, 'id', [
			'alias'    => 'subdistrict',
			'reusable' => true,
		]);
		$this->hasMany('id', User::class, 'village_id', ['alias' => 'users']);
		$this->hasManyToMany('id', CoverageArea::class, 'village_id', 'user_id', User::class, 'id', ['alias' => 'merchants']);
	}

	function setName($name) {
		$this->name = $this->getDI()->getFilter()->sanitize($name, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		$validator->add(['subdistrict_id', 'name'], new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}