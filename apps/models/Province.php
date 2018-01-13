<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Province extends ModelBase {
	public $id;
	public $name;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'provinces';
	}

	function initialize() {
		parent::initialize();
		$this->hasMany('id', City::class, 'province_id', [
			'alias'      => 'cities',
			'foreignKey' => [
				'message' => 'propinsi tidak dapat dihapus karena memiliki kota / kabupaten',
			],
		]);
	}

	function setName($name) {
		$this->name = $this->getDI()->getFilter()->sanitize($name, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		$validator->add('name', new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}