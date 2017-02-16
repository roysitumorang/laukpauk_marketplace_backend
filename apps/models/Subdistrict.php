<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Subdistrict extends ModelBase {
	public $id;
	public $city_id;
	public $name;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'subdistricts';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('city_id', 'Application\Models\City', 'id', [
			'alias'    => 'city',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\Village', 'subdistrict_id', [
			'alias'      => 'villages',
			'foreignKey' => [
				'message' => 'kecamatan tidak dapat dihapus karena memiliki kelurahan / desa',
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
