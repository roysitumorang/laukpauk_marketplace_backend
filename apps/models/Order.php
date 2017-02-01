<?php

namespace Application\Models;

use Application\Models\Notification;
use Application\Models\NotificationTemplate;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Date;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Order extends ModelBase {
	public $id;
	public $code;
	public $name;
	public $email;
	public $address;
	public $village_id;
	public $mobile_phone;
	public $final_bill;
	public $status;
	public $merchant_id;
	public $buyer_id;
	public $admin_fee;
	public $original_bill;
	public $ip_address;
	public $coupon_id;
	public $scheduled_delivery;
	public $actual_delivery;
	public $note;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	const STATUS = [
		-1 => 'CANCELLED',
		0  => 'HOLD',
		1  => 'COMPLETED',
	];

	function getSource() {
		return 'orders';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('merchant_id', 'Application\Models\User', 'id', [
			'alias'      => 'merchant',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'penjual harus diisi',
			],
		]);
		$this->belongsTo('buyer_id', 'Application\Models\User', 'id', [
			'alias'      => 'buyer',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'pembeli harus diisi',
			],
		]);
		$this->belongsTo('coupon_id', 'Application\Models\Coupon', 'id', [
			'alias'      => 'coupon',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => true],
		]);
		$this->hasMany('id', 'Application\Models\OrderItem', 'order_id', [
			'alias' => 'items',
		]);
	}

	function beforeValidationOnCreate() {
		parent::beforeValidationOnCreate();
		$this->status     = array_search('HOLD', static::STATUS);
		$this->ip_address = $this->getDI()->getRequest()->getClientAddress();
		$this->admin_fee  = Setting::findFirstByName('admin_fee')->value;
		do {
			$this->code = random_int(111111, 999999);
			if (!static::findFirstByCode($this->code)) {
				break;
			}
		} while (1);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'mobile_phone', 'address', 'village_id', 'scheduled_delivery'], new PresenceOf([
			'message' => [
				'name'               => 'nama harus diisi',
				'mobile_phone'       => 'nomor HP harus diisi',
				'address'            => 'alamat harus diisi',
				'village_id'         => 'kelurahan harus diisi',
				'scheduled_delivery' => 'waktu pengantaran harus diisi',
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
			$validator->add('scheduled_delivery', new Date([
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
		$this->note            = $this->note            ?: null;
	}

	function beforeDelete() {
		foreach ($this->items as $item) {
			$item->delete();
		}
	}

	function afterCreate() {
		$this->merchant->update(['deposit' => $this->merchant->deposit - $this->final_bill]);
		$admin_new_order_template       = NotificationTemplate::findFirstByName('admin new order');
		$admin_notification             = new Notification;
		$admin_notification->subject    = $admin_new_order_template->subject;
		$admin_notification->link       = $admin_new_order_template->url . $this->id;
		$admin_notification->created_by = $this->created_by;
		$admins                         = User::find([
			'role_id IN ({role_ids:array}) AND 1',
			'bind' => ['role_ids' => [Role::SUPER_ADMIN, Role::ADMIN]],
		]);
		foreach ($admins as $admin) {
			$recipients[] = $admin;
		}
		$admin_notification->recipients    = $recipients;
		$admin_notification->create();
		$merchant_new_order_template       = NotificationTemplate::findFirstByName('api new order');
		$merchant_notification             = new Notification;
		$merchant_notification->subject    = $merchant_new_order_template->subject;
		$merchant_notification->link       = $merchant_new_order_template->url . $this->id;
		$merchant_notification->created_by = $this->created_by;
		$merchant_notification->recipients = [$this->merchant];
		$merchant_notification->create();
		$tokens = [];
		foreach ($this->merchant->devices as $device) {
			$tokens[] = $device->token;
		}
		$this->_sendPushNotification($tokens, [
			'title'   => 'Order Baru #' . $this->code,
			'content' => 'Order Baru #' . $this->code,
		]);
	}

	function cancel() {
		$this->update(['status' => array_search('CANCELLED', static::STATUS)]);
		$this->merchant->update(['deposit' => $this->merchant->deposit + $this->final_bill]);
		$admin_new_order_template       = NotificationTemplate::findFirstByName('admin order cancelled');
		$admin_notification             = new Notification;
		$admin_notification->subject    = $admin_new_order_template->subject;
		$admin_notification->link       = $admin_new_order_template->url . $this->id;
		$admin_notification->created_by = $this->merchant->id;
		$admins                         = User::find([
			'role_id IN ({role_ids:array}) AND 1',
			'bind' => ['role_ids' => [Role::SUPER_ADMIN, Role::ADMIN]],
		]);
		foreach ($admins as $admin) {
			$recipients[] = $admin;
		}
		$admin_notification->recipients    = $recipients;
		$admin_notification->create();
		$merchant_new_order_template       = NotificationTemplate::findFirstByName('api order cancelled');
		$merchant_notification             = new Notification;
		$merchant_notification->subject    = $merchant_new_order_template->subject;
		$merchant_notification->link       = $merchant_new_order_template->url . $this->id;
		$merchant_notification->created_by = $this->merchant->id;
		$merchant_notification->recipients = [$this->buyer];
		$merchant_notification->create();
		$tokens = [];
		foreach ($this->buyer->devices as $device) {
			$tokens[] = $device->token;
		}
		$this->_sendPushNotification($tokens, [
			'title'   => 'Order #' . $this->code . ' Dibatalkan',
			'content' => 'Order #' . $this->code . ' Dibatalkan',
		]);
	}

	function complete() {
		$this->update([
			'status'          => array_search('COMPLETED', static::STATUS),
			'actual_delivery' => $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s'),
		]);
		$this->merchant->update(['deposit' => $this->merchant->deposit + $this->final_bill]);
		$admin_new_order_template       = NotificationTemplate::findFirstByName('admin order delivered');
		$admin_notification             = new Notification;
		$admin_notification->subject    = $admin_new_order_template->subject;
		$admin_notification->link       = $admin_new_order_template->url . $this->id;
		$admin_notification->created_by = $this->merchant->id;
		$admins                         = User::find([
			'role_id IN ({role_ids:array}) AND 1',
			'bind' => ['role_ids' => [Role::SUPER_ADMIN, Role::ADMIN]],
		]);
		foreach ($admins as $admin) {
			$recipients[] = $admin;
		}
		$admin_notification->recipients    = $recipients;
		$admin_notification->create();
		$merchant_new_order_template       = NotificationTemplate::findFirstByName('api order delivered');
		$merchant_notification             = new Notification;
		$merchant_notification->subject    = $merchant_new_order_template->subject;
		$merchant_notification->link       = $merchant_new_order_template->url . $this->id;
		$merchant_notification->created_by = $this->merchant->id;
		$merchant_notification->recipients = [$this->buyer];
		$merchant_notification->create();
		$tokens = [];
		foreach ($this->buyer->devices as $device) {
			$tokens[] = $device->token;
		}
		$this->_sendPushNotification($tokens, [
			'title'   => 'Order #' . $this->code . ' Diterima',
			'content' => 'Order #' . $this->code . ' Diterima',
		]);
	}
}