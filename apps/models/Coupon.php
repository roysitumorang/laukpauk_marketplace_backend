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
	public $user_id;
	public $code;
	public $effective_date;
	public $expiry_date;
	public $price_discount;
	public $discount_type;
	public $status;
	public $multiple_use;
	public $minimum_purchase;
	public $release_id;
	public $maximum_usage;
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
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
		$this->belongsTo('release_id', 'Application\Models\Release', 'id', [
			'alias'    => 'release',
			'reusable' => true,
		]);
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
		$validator->add(['code', 'effective_date', 'expiry_date', 'price_discount', 'discount_type', 'status', 'multiple_use', 'minimum_purchase'], new PresenceOf([
			'message' => [
				'code'             => 'kode kupon harus diisi',
				'effective_date'   => 'tanggal berlaku harus diisi',
				'expiry_date'      => 'tanggal expired harus diisi',
				'price_discount'   => 'diskon harus diisi',
				'discount_type'    => 'tipe diskon harus diisi',
				'status'           => 'status harus diisi',
				'multiple_use'     => 'penggunaan harus diisi',
				'minimum_purchase' => 'belanja minimal harus diisi',
				'maximum_usage'    => 'pemakaian maksimal harus diisi',
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
		$validator->add(['price_discount', 'minimum_purchase', 'maximum_usage'], new Digit([
			'message' => [
				'price_discount'   => 'diskon tidak valid',
				'minimum_purchase' => 'belanja minimal tidak valid',
				'maximum_usage'    => 'pemakaian maksimal tidak valid',
			],
		]));
		$validator->add(['discount_type', 'multiple_use', 'status'], new InclusionIn([
			'domain'  => [
				'discount_type' => array_keys(static::DISCOUNT_TYPES),
				'multiple_use'  => array_keys(static::USAGE_TYPES),
				'status'        => array_keys(static::STATUS),
			],
			'message' => [
				'discount_type' => 'tipe diskon tidak valid',
				'multiple_use'  => 'penggunaan tidak valid',
				'status'        => 'status tidak valid',
			],
		]));
		return $this->validate($validator);
	}
}
