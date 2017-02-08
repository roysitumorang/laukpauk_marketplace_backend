<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Date;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;

class Coupon extends ModelBase {
	const DISCOUNT_TYPES = ['%', 'Rp'];
	const STATUS         = ['Hold', 'Aktif'];
	const USAGE_TYPES    = [
		'Sekali Pakai',
		'Berkali-kali',
	];

	public $id;
	public $code;
	public $effective_date;
	public $expiry_date;
	public $discount_amount;
	public $discount_type;
	public $status;
	public $usage;
	public $minimum_purchase;
	public $description;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'coupons';
	}

	function initialize() {
		parent::initialize();
		$this->hasMany('id', 'Application\Models\Order', 'coupon_id', ['alias' => 'orders']);
		$this->hasManyToMany('id', 'Application\Models\CouponUser', 'coupon_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'users']);
		$this->hasManyToMany('id', 'Application\Models\CouponVillage', 'coupon_id', 'village_id', 'Application\Models\Village', 'id', ['alias' => 'villages']);
	}

	function setCode($code) {
		if (!$this->id) {
			$this->code = $code;
		}
	}

	function setDescription($description) {
		$this->description = $description ?: null;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['code', 'effective_date', 'expiry_date', 'discount_amount', 'discount_type', 'status', 'usage', 'minimum_purchase'], new PresenceOf([
			'message' => [
				'code'             => 'kode kupon harus diisi',
				'effective_date'   => 'tanggal berlaku harus diisi',
				'expiry_date'      => 'tanggal expired harus diisi',
				'discount_amount'  => 'diskon harus diisi',
				'discount_type'    => 'tipe diskon harus diisi',
				'status'           => 'status harus diisi',
				'usage'            => 'penggunaan harus diisi',
				'minimum_purchase' => 'belanja minimal harus diisi',
			],
		]));
		$validator->add(['effective_date', 'expiry_date'], new Date([
			'format'  => [
				'effective_date' => 'Y-m-d',
				'expiry_date'    => 'Y-m-d',
			],
			'message' => [
				'effective_date' => 'tanggal berlaku tidak valid',
				'expiry_date'    => 'tanggal expired tidak valid',
			],
		]));
		$validator->add(['discount_amount', 'minimum_purchase'], new Digit([
			'message' => [
				'discount_amount'  => 'diskon tidak valid',
				'minimum_purchase' => 'belanja minimal tidak valid',
			],
		]));
		$validator->add(['discount_type', 'usage', 'status'], new InclusionIn([
			'domain'  => [
				'discount_type' => array_keys(static::DISCOUNT_TYPES),
				'usage'         => array_keys(static::USAGE_TYPES),
				'status'        => array_keys(static::STATUS),
			],
			'message' => [
				'discount_type' => 'tipe diskon tidak valid',
				'usage'         => 'penggunaan tidak valid',
				'status'        => 'status tidak valid',
			],
		]));
		return $this->validate($validator);
	}
}
