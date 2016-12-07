<?php

namespace Application\Models;

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
}