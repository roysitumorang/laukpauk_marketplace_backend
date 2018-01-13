<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\{InclusionIn, PresenceOf, Uniqueness};

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

	private $_filter;

	function getSource() {
		return 'cities';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('province_id', Province::class, 'id', [
			'alias'    => 'province',
			'reusable' => true,
		]);
		$this->hasMany('id', Subdistrict::class, 'city_id', [
			'alias'      => 'subdistricts',
			'foreignKey' => [
				'message' => 'kota / kabupaten tidak dapat dihapus karena memiliki kecamatan',
			],
		]);
		$this->_filter = $this->getDI()->getFilter();
	}

	function setType($type) {
		$this->type = $this->_filter->sanitize($type, ['string', 'trim']);
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
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
		$validator->add(['province_id', 'name', 'type'], new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'kabupaten / kota sudah ada',
		]));
		return $this->validate($validator);
	}
}