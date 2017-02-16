<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

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
		$this->hasMany('id', 'Application\Models\City', 'province_id', [
			'alias'      => 'cities',
			'foreignKey' => [
				'message' => 'propinsi tidak dapat dihapus karena memiliki kota / kabupaten',
			],
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		return $this->validate($validator);
	}
}