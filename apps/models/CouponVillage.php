<?php

namespace Application\Models;

use Application\Models\ModelBase;

class CouponVillage extends ModelBase {
	public $id;
	public $coupon_id;
	public $village_id;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'coupon_villages';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('coupon_id', 'Application\Models\Coupon', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('village_id', 'Application\Models\Village', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}