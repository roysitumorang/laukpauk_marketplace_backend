<?php

namespace Application\Models;

class City extends ModelBase {
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
}