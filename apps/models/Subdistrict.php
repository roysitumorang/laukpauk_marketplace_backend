<?php

namespace Application\Models;

class Subdistrict extends BaseModel {
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
	}
}
