<?php

namespace Application\Models;

use Application\Models\ModelBase;

class CouponUser extends ModelBase {
	public $id;
	public $coupon_id;
	public $user_id;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'coupon_users';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('coupon_id', 'Application\Models\Coupon', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}