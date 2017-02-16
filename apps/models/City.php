<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;

class City extends ModelBase {
	const TYPES = ['Kabupaten', 'Kota'];

	public $id;
	public $province_id;
	public $type;
	public $name;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'cities';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('province_id', 'Application\Models\Province', 'id', [
			'alias'    => 'province',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\Subdistrict', 'city_id', [
			'alias'      => 'subdistricts',
			'foreignKey' => [
				'message' => 'kota / kabupaten tidak dapat dihapus karena memiliki kecamatan',
			],
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'type'], new PresenceOf([
			'message' => [
				'name' => 'nama harus diisi',
				'type' => 'tipe harus diisi',
			],
		]));
		$validator->add('type', new InclusionIn([
			'message' => 'tipe yang valid ' . implode(' atau ', static::TYPES),
			'domain'  => static::TYPES,
		]));
		return $this->validate($validator);
	}
}