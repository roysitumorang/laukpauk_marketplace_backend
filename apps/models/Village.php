<?php

namespace Application\Models;

class Village extends BaseModel {
	public $id;
	public $subdistrict_id;
	public $name;
	public $zip_code;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'villages';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('subdistrict_id', 'Application\Models\Subdistrict', 'id', [
			'alias'    => 'subdistrict',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\ServiceArea', 'village_id', ['alias' => 'service_areas']);
	}
}
