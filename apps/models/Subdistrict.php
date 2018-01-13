<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

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
		$this->belongsTo('city_id', City::class, 'id', [
			'alias'    => 'city',
			'reusable' => true,
		]);
		$this->hasMany('id', Village::class, 'subdistrict_id', [
			'alias'      => 'villages',
			'foreignKey' => [
				'message' => 'kecamatan tidak dapat dihapus karena memiliki kelurahan / desa',
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
		$validator->add(['city_id', 'name'], new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}
