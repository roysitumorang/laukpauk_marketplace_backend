<?php

namespace Application\Models;

use Application\Models\Notification;
use Application\Models\NotificationTemplate;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Date;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
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
	public $shipping_cost;
	public $ip_address;
	public $coupon_id;
	public $discount;
	public $scheduled_delivery;
	public $actual_delivery;
	public $note;
	public $cancellation_reason;
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
				'message'    => 'merchant harus diisi',
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
		$this->hasMany('id', 'Application\Models\OrderProduct', 'order_id', [
			'alias' => 'orderProducts',
		]);
		$this->hasManyToMany('id', 'Application\Models\OrderProduct', 'order_id', 'product_id', 'Application\Models\Product', 'id', ['alias' => 'products']);
	}

	function beforeValidationOnCreate() {
		parent::beforeValidationOnCreate();
		$this->status     = array_search('HOLD', static::STATUS);
		$this->ip_address = $this->getDI()->getRequest()->getClientAddress();
		$this->admin_fee  = $this->merchant->admin_fee ?: Setting::findFirstByName('admin_fee')->value;
		do {
			$this->code = random_int(111111, 999999);
			if (!static::findFirstByCode($this->code)) {
				break;
			}
		} while (1);
		$this->shipping_cost = $this->shipping_cost ?? 0;
		$this->discount      = $this->discount ?? 0;
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
		$validator->add('name', new Regex([
			'pattern' => "/^[A-Za-z'\. ]+$/",
			'message' => 'nama tidak valid',
		]));
		$validator->add('code', new Uniqueness([
			'message' => 'kode order sudah ada',
		]));
		$validator->add('status', new InclusionIn([
			'domain'  => array_keys(static::STATUS),
			'message' => 'status yang valid HOLD, CANCELLED atau COMPLETED',
		]));
		if (!$this->id) {
			$validator->add('scheduled_delivery', new Callback([
				'callback' => function($data) {
					try {
						$scheduled_delivery = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data->scheduled_delivery, new DateTimeZone($this->getDI()->getConfig()->timezone));
						$delivery_day       = $scheduled_delivery->format('l');
					} catch (Exception $e) {
						return false;
					}
					$valid_dates = [];
					foreach (new DatePeriod($this->getDI()->getCurrentDatetime(), new DateInterval('P1D'), 1) as $date) {
						$valid_dates[] = $date->format('Y-m-d');
					}
					return in_array($scheduled_delivery->format('Y-m-d'), $valid_dates) &&
						(($delivery_day == 'Sunday' && $this->merchant->open_on_sunday) ||
						($delivery_day == 'Monday' && $this->merchant->open_on_monday) ||
						($delivery_day == 'Tuesday' && $this->merchant->open_on_tuesday) ||
						($delivery_day == 'Wednesday' && $this->merchant->open_on_wednesday) ||
						($delivery_day == 'Thursday' && $this->merchant->open_on_thursday) ||
						($delivery_day == 'Friday' && $this->merchant->open_on_friday) ||
						($delivery_day == 'Saturday' && $this->merchant->open_on_saturday)) &&
						in_array($scheduled_delivery->format('G'), $this->merchant->delivery_hours) &&
						($scheduled_delivery->format('Y-m-d') === $this->getDI()->getCurrentDatetime()->format('Y-m-d')
						? ($scheduled_delivery->format('G') >= $this->getDI()->getCurrentDatetime()->format('G') + ($this->getDI()->getCurrentDatetime()->format('i') > 29 ? 2 : 1))
						: true);
				},
				'message' => 'tanggal jam pengantaran tidak valid',
			]));
		} else if (array_search('CANCELLED', static::STATUS) === $this->status) {
			$validator->add('cancellation_reason', new PresenceOf([
				'message' => 'alasan pembatalan harus diisi',
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
		$session = $this->getDI()->getSession();
		$this->buyer->update([
			'name'       => $this->name,
			'address'    => $this->address,
			'updated_by' => $session && $session->has('user_id') ? $session->get('user_id') : $this->created_by,
		]);
		$this->_push_notification('new order', [$this->merchant]);
	}

	function cancel($cancellation_reason) {
		$session = $this->getDI()->getSession();
		$this->update([
			'status'              => array_search('CANCELLED', static::STATUS),
			'cancellation_reason' => $cancellation_reason ?: null,
			'updated_by'          => $session && $session->has('user_id') ? $session->get('user_id') : $this->created_by,
		]);
		$this->_push_notification('cancelled order', [$this->buyer]);
	}

	function complete() {
		$session = $this->getDI()->getSession();
		foreach ($this->orderProducts as $item) {
			$user_product = UserProduct::findFirst(['user_id = ?0 AND product_id = ?1', 'bind' => [$this->merchant_id, $item->product_id]]);
			$user_product->update(['stock' => max(0, $user_product->stock - $item->quantity)]);
		}
		$this->update([
			'status'          => array_search('COMPLETED', static::STATUS),
			'actual_delivery' => $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s'),
			'updated_by'      => $session && $session->has('user_id') ? $session->get('user_id') : $this->created_by,
		]);
		$this->merchant->update([
			'deposit'    => $this->merchant->deposit - $this->admin_fee,
			'updated_by' => $session && $session->has('user_id') ? $session->get('user_id') : $this->created_by,
		]);
		$this->_push_notification('delivered order', [$this->buyer]);
	}

	private function _push_notification($template_name, $recipients) {
		$template     = NotificationTemplate::findFirstByName($template_name);
		$notification = new Notification;
		$admins       = User::find([
			'role_id IN ({role_ids:array}) AND status = 1',
			'bind' => ['role_ids' => [Role::SUPER_ADMIN, Role::ADMIN]],
		]);
		foreach ($admins as $admin) {
			$recipients[] = $admin;
		}
		foreach (['title', 'admin_target_url', 'merchant_target_url', 'old_mobile_target_url', 'new_mobile_target_url'] as $attribute) {
			$notification->$attribute = strtr($template->$attribute, ['{order_id}' => $this->code]);
		}
		$notification->message                      = $notification->title;
		$notification->new_mobile_target_parameters = json_encode(['orderId' => $this->id]);
		$notification->created_by                   = $this->created_by;
		$notification->push($recipients);
	}
}