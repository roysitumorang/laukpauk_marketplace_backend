<?php

namespace Application\Models;

class Village extends ModelBase {
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
		$this->hasMany('id', 'Application\Models\User', 'village_id', ['alias' => 'users']);
		$this->hasManyToMany('id', 'Application\Models\ServiceArea', 'village_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'merchants']);
		$this->hasManyToMany('id', 'Application\Models\CouponVillage', 'village_id', 'coupon_id', 'Application\Models\Coupon', 'id', ['alias' => 'coupons']);
	}
}