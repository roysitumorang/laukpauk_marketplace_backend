<?php

namespace Application\Models;

use Application\Models\ModelBase;

class CouponUser extends ModelBase {
	public $id;
	public $coupon_id;
	public $user_id;
	public $created_by;
	public $created_at;

	function initialize() {
		parent::initialize();
		$this->belongsTo('coupon_id', 'Application\Models\Coupon', 'id', [
			'alias'      => 'coupon',
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'      => 'user',
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}