<?php

namespace Application\Models;

use Application\Models\BaseModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Date;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class OrderItem extends BaseModel {
	public $id;
	public $code;
	public $name;
	public $email;
	public $address;
	public $village_id;
	public $phone;
	public $mobile;
	public $tracking_number;
	public $payment;
	public $final_bill;
	public $status;
	public $merchant_id;
	public $buyer_id;
	public $admin_fee;
	public $original_bill;
	public $ip_address;
	public $affiliate_user_id;
	public $shipping_fee;
	public $shipping_line;
	public $detail;
	public $coupon_id;
	public $estimated_delivery;
	public $actual_delivery;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	const STATUS = [
		-1 => 'CANCELLED',
		0  => 'HOLD',
		1  => 'COMPLETED',
	];

	const ADMIN_FEE = 2000;

	function getSource() {
		return 'orders';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('merchant_id', 'Application\Models\User', 'id', [
			'alias'    => 'merchant',
			'reusable' => true,
		]);
		$this->belongsTo('buyer_id', 'Application\Models\User', 'id', [
			'alias'    => 'buyer',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\OrderItem', 'order_id', [
			'alias' => 'items',
		]);
	}

	function beforeValidationOnCreate() {
		$this->status     = array_search(static::STATUS, 'HOLD');
		$this->ip_address = $this->getDI()->getRequest()->getClientAddress();
		$this->admin_fee  = static::ADMIN_FEE;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['code', 'name', 'phone', 'address', 'village_id', 'status', 'merchant_id', 'buyer_id', 'estimated_delivery'], new PresenceOf([
			'message' => [
				'code'               => 'kode order harus diisi',
				'name'               => 'nama harus diisi',
				'phone'              => 'nomor HP harus diisi',
				'address'            => 'alamat harus diisi',
				'village_id'         => 'kelurahan harus diisi',
				'status'             => 'status harus diisi',
				'merchant_id'        => 'penjual harus diisi',
				'buyer_id'           => 'pembeli harus diisi',
				'estimated_delivery' => 'waktu pengantaran harus diisi',
			],
		]));
		$validator->add('code', new Uniqueness([
			'message' => 'kode order sudah ada',
		]));
		$validator->add('status', new InclusionIn([
			'domain'  => array_keys(static::STATUS),
			'message' => 'status yang valid HOLD, CANCELLED atau COMPLETED',
		]));
		if (!$this->id) {
			$validator->add('estimated_delivery', new Date([
				'format'  => 'Y-m-d H:i:s',
				'message' => 'jam pengantaran tidak valid',
			]));
		}
		if ($this->id && $this->actual_delivery) {
			$validator->add('actual_delivery', new Date([
				'format'  => 'Y-m-d H:i:s',
				'message' => 'jam pengantaran aktual tidak valid',
			]));
		}
		return $this->validate($validator);
	}

	function beforeSave() {
		$this->actual_delivery = $this->actual_delivery ?: null;
	}

	function beforeDelete() {
		foreach ($this->items as $item) {
			$item->delete();
		}
	}

	function cancel() {
		return $this->update(['status' => static::STATUS['CANCELLED']]);
	}

	function complete() {
		return $this->update([
			'status'          => static::STATUS['COMPLETED'],
			'actual_delivery' => $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s'),
		]);
	}
}