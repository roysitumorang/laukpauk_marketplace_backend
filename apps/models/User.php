<?php

namespace Application\Models;

use Imagick;
use Phalcon\Http\Request\File;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\{Callback, Between, Confirmation, Date, Email, Numericality, PresenceOf, Regex, Uniqueness};

class User extends ModelBase {
	const STATUS = [
		0  => 'HOLD',
		1  => 'ACTIVE',
		-1 => 'SUSPENDED',
	];
	const GENDERS        = ['Pria', 'Wanita'];
	const BUSINESS_HOURS = [
		'opening' => 6,
		'closing' => 22,
	];

	public $id;
	public $role_id;
	public $api_key;
	public $merchant_note;
	public $minimum_purchase;
	public $admin_fee;
	public $accumulation_divisor;
	public $name;
	public $email;
	public $password;
	public $new_password;
	public $new_password_confirmation;
	public $change_password;
	public $address;
	public $village_id;
	public $mobile_phone;
	public $device_token;
	public $status;
	public $activated_at;
	public $activation_token;
	public $password_reset_token;
	public $deposit;
	public $company;
	public $registration_ip;
	public $gender;
	public $date_of_birth;
	public $avatar;
	public $new_avatar;
	public $thumbnails;
	public $open_on_sunday;
	public $open_on_monday;
	public $open_on_tuesday;
	public $open_on_wednesday;
	public $open_on_thursday;
	public $open_on_friday;
	public $open_on_saturday;
	public $business_opening_hour;
	public $business_closing_hour;
	public $delivery_hours;
	public $latitude;
	public $longitude;
	public $max_delivery_distance;
	public $free_delivery_distance;
	public $delivery_rate;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'users';
	}

	function onConstruct() {
		$di                   = $this->getDI();
		$this->_upload_config = $di->getConfig()->upload;
		$this->_filter        = $di->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('role_id', Role::class, 'id', [
			'alias'      => 'role',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'role harus diisi',
			],
		]);
		$this->belongsTo('village_id', Village::class, 'id', [
			'alias'      => 'village',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'kelurahan harus diisi',
			],
		]);
		$this->hasMany('id', LoginHistory::class, 'user_id', ['alias' => 'loginHistory']);
		$this->hasMany('id', Order::class, 'buyer_id', ['alias' => 'buyerOrders']);
		$this->hasMany('id', Order::class, 'merchant_id', ['alias' => 'merchantOrders']);
		$this->hasManyToMany('id', UserProduct::class, 'user_id', 'product_id', Product::class, 'id', ['alias' => 'products']);
		$this->hasManyToMany('id', CoverageArea::class, 'user_id', 'village_id', Village::class, 'id', ['alias' => 'coverageAreas']);
		$this->hasManyToMany('id', MessageRecipient::class, 'user_id', 'message_id', Message::class, 'id', ['alias' => 'messages']);
		$this->hasManyToMany('id', NotificationRecipient::class, 'user_id', 'notification_id', Notification::class, 'id', ['alias' => 'notifications']);
		$this->hasMany('id', Device::class, 'user_id', ['alias' => 'devices']);
		$this->hasManyToMany('id', SmsRecipient::class, 'user_id', 'sms_id', Sms::class, 'id', ['alias' => 'sms']);
		$this->hasMany('id', Notification::class, 'user_id', ['alias' => 'ownNotifications']);
		$this->hasMany('id', Sms::class, 'user_id', ['alias' => 'ownSms']);
		$this->hasMany('id', Payment::class, 'user_id', ['alias' => 'payments']);
		$this->hasMany('id', SalePackage::class, 'user_id', ['alias' => 'salePackages']);
	}

	function setMerchantNote($merchant_note) {
		$this->merchant_note = $this->_filter->sanitize($merchant_note, 'trim') ?: null;
	}

	function setMinimumPurchase($minimum_purchase) {
		$this->minimum_purchase = filter_var($minimum_purchase, FILTER_VALIDATE_INT) ?: 0;
	}

	function setAdminFee($admin_fee) {
		$this->admin_fee = filter_var($admin_fee, FILTER_VALIDATE_INT) ?: 0;
	}

	function setAccumulationDivisor($accumulation_divisor) {
		$this->accumulation_divisor = filter_var($accumulation_divisor, FILTER_VALIDATE_INT) ?: 0;
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setEmail($email) {
		if ($email) {
			$this->email = $this->_filter->sanitize($email, ['string', 'trim']);
		}
	}

	function setNewPassword($new_password) {
		if ($new_password) {
			$this->new_password = $this->_filter->sanitize($new_password, ['string', 'trim']);
		}
	}

	function setNewPasswordConfirmation($new_password_confirmation) {
		if ($new_password_confirmation) {
			$this->new_password_confirmation = $this->_filter->sanitize($new_password_confirmation, ['string', 'trim']);
		}
	}

	function setChangePassword(bool $change_password = false) {
		$this->change_password = $change_password;
	}

	function setAddress($address) {
		if ($address) {
			$this->address = $this->_filter->sanitize($address, ['string', 'trim']);
		}
	}

	function setMobilePhone($mobile_phone) {
		$this->mobile_phone = $this->_filter->sanitize($mobile_phone, 'int');
	}

	function setDeviceToken($device_token) {
		$this->device_token = $device_token;
	}

	function setStatus($status) {
		$this->status = $this->_filter->sanitize($status, 'int');
	}

	function setActivatedAt($activated_at) {
		if ($activated_at) {
			$this->activated_at = $this->_filter->sanitize($activated_at, ['string', 'trim']);
		}
	}

	function setActivationToken($activation_token) {
		$this->activation_token = $activation_token;
	}

	function setPasswordResetToken($password_reset_token) {
		$this->password_reset_token = $password_reset_token;
	}

	function setDeposit($deposit) {
		$this->deposit = filter_var($deposit, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setCompany($company) {
		if ($company) {
			$this->company = $this->_filter->sanitize($company, ['string', 'trim']);
		}
	}

	function setGender($gender) {
		if ($gender && in_array($gender, static::GENDERS)) {
			$this->gender = $gender;
		}
	}

	function setDateOfBirth($date_of_birth) {
		if ($date_of_birth) {
			$this->date_of_birth = $this->_filter->sanitize($date_of_birth, ['string', 'trim']);
		}
	}

	function setNewAvatar(File $new_avatar) {
		if ($new_avatar->getTempName() && $new_avatar->getSize() && !$new_avatar->getError()) {
			$this->new_avatar = $new_avatar;
		}
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = $thumbnails;
	}

	function setOpenOnSunday($open_on_sunday) {
		$this->open_on_sunday = filter_var($open_on_sunday, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setOpenOnMonday($open_on_monday) {
		$this->open_on_monday = filter_var($open_on_monday, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setOpenOnTuesday($open_on_tuesday) {
		$this->open_on_tuesday = filter_var($open_on_tuesday, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setOpenOnWednesday($open_on_wednesday) {
		$this->open_on_wednesday = filter_var($open_on_wednesday, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setOpenOnThursday($open_on_thursday) {
		$this->open_on_thursday = filter_var($open_on_thursday, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setOpenOnFriday($open_on_friday) {
		$this->open_on_friday = filter_var($open_on_friday, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setOpenOnSaturday($open_on_saturday) {
		$this->open_on_saturday = filter_var($open_on_saturday, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
	}

	function setBusinessOpeningHour($business_opening_hour) {
		if ($business_opening_hour) {
			$this->business_opening_hour = $business_opening_hour;
		}
	}

	function setBusinessClosingHour($business_closing_hour) {
		if ($business_closing_hour) {
			$this->business_closing_hour = $business_closing_hour;
		}
	}

	function setDeliveryHours($delivery_hours) {
		if ($delivery_hours) {
			$this->delivery_hours = array_filter($delivery_hours, function($v, $k) {
				return $v >= $this->business_opening_hour && $v <= $this->business_closing_hour;
			}, ARRAY_FILTER_USE_BOTH);
		}
	}

	function setLatitude($latitude) {
		$this->latitude = $this->_filter->sanitize($latitude, 'float') ?: null;
	}

	function setLongitude($longitude) {
		$this->longitude = $this->_filter->sanitize($longitude, 'float') ?: null;
	}

	function setMaxDeliveryDistance($max_delivery_distance) {
		$this->max_delivery_distance = $this->_filter->sanitize($max_delivery_distance, 'int') ?: 0;
	}

	function setFreeDeliveryDistance($free_delivery_distance) {
		$this->free_delivery_distance = $this->_filter->sanitize($free_delivery_distance, 'int') ?: 0;
	}

	function setDeliveryRate($delivery_rate) {
		$this->delivery_rate = $this->_filter->sanitize($delivery_rate, 'int') ?: 0;
	}

	function beforeValidationOnCreate() {
		parent::beforeValidationOnCreate();
		$random                = new Random;
		$this->status          = array_search('HOLD', static::STATUS);
		$this->registration_ip = $this->getDI()->getRequest()->getClientAddress();
		do {
			$this->activation_token = $random->hex(16);
			if (!static::findFirstByActivationToken($this->activation_token)) {
				break;
			}
		} while (1);
		do {
			$this->api_key = $random->hex(16);
			if (!static::findFirstByApiKey($this->api_key)) {
				break;
			}
		} while (1);
		$this->open_on_sunday    = $this->open_on_sunday    ?? 0;
		$this->open_on_monday    = $this->open_on_monday    ?? 0;
		$this->open_on_tuesday   = $this->open_on_tuesday   ?? 0;
		$this->open_on_wednesday = $this->open_on_wednesday ?? 0;
		$this->open_on_thursday  = $this->open_on_thursday  ?? 0;
		$this->open_on_friday    = $this->open_on_friday    ?? 0;
		$this->open_on_saturday  = $this->open_on_saturday  ?? 0;
	}

	function beforeValidation() {
		$this->role_id = $this->role->id;
		if (!$this->id || $this->new_password) {
			$this->password = password_hash($this->new_password, PASSWORD_DEFAULT);
		}
		if ($this->role_id != Role::MERCHANT) {
			$this->minimum_purchase = 0;
			$this->admin_fee        = 0;
			$this->merchant_note    = null;
		}
		if (!is_int($this->accumulation_divisor)) {
			$this->accumulation_divisor = 0;
		}
		if (!$this->device_token) {
			$this->device_token = null;
		}
	}

	function validation() {
		$validator = new Validation;
		$max_size  = $this->_upload_config->max_size;
		$validator->add(['name', 'mobile_phone', 'deposit'], new PresenceOf([
			'message' => [
				'name'         => 'nama harus diisi',
				'mobile_phone' => 'nomor HP harus diisi',
				'deposit'      => 'deposit harus diisi',
			],
		]));
		$validator->add('name', new Regex([
			'pattern' => "/^[A-Za-z'\. ]+$/",
			'message' => 'nama tidak valid',
		]));
		$validator->add('mobile_phone', new Regex([
			'pattern' => '/^(\+?0?62)?0?8\d{8,11}$/',
			'message' => 'nomor HP tidak valid',
		]));
		if ($this->device_token) {
			$validator->add('device_token', new Uniqueness([
				'message' => 'token notifikasi sudah terdaftar',
			]));
		}
		if ($this->role_id == Role::MERCHANT) {
			$validator->add('company', new PresenceOf(['message' => 'nama toko harus diisi']));
			$validator->add(['business_opening_hour', 'business_closing_hour'], new PresenceOf([
				'message' => [
					'business_opening_hour' => 'jam mulai operasional harus diisi',
					'business_closing_hour' => 'jam tutup operasional harus diisi',
				],
			]));
			$validator->add(['business_opening_hour', 'business_closing_hour'], new Between([
				'minimum' => [
					'business_opening_hour' => static::BUSINESS_HOURS['opening'],
					'business_closing_hour' => static::BUSINESS_HOURS['opening'],
				],
				'maximum' => [
					'business_opening_hour' => static::BUSINESS_HOURS['closing'],
					'business_closing_hour' => static::BUSINESS_HOURS['closing'],
				],
				'message' => [
					'business_opening_hour' => 'jam mulai operasional antara ' . static::BUSINESS_HOURS['opening'] . ' dan ' . static::BUSINESS_HOURS['closing'],
					'business_closing_hour' => 'jam mulai operasional antara ' . static::BUSINESS_HOURS['opening'] . ' dan ' . static::BUSINESS_HOURS['closing'],
				],
			]));
			$validator->add('minimum_purchase', new Callback([
				'callback' => function($data) {
					return filter_var($data->minimum_purchase, FILTER_VALIDATE_INT) !== false && $data->minimum_purchase >= 0;
				},
				'message'  => 'minimal order harus diisi angka, minimal 0',
			]));
			$validator->add('admin_fee', new Callback([
				'callback' => function($data) {
					return filter_var($data->admin_fee, FILTER_VALIDATE_INT) !== false && $data->admin_fee >= 0;
				},
				'message'  => 'biaya administrasi harus diisi angka, minimal 0',
			]));
		}
		if ($this->getSnapshotData()['mobile_phone'] != $this->mobile_phone) {
			$validator->add('mobile_phone', new Uniqueness([
				'message' => 'nomor HP sudah terdaftar',
			]));
		}
		if (!$this->id || $this->change_password || $this->new_password || $this->new_password_confirmation) {
			$validator->add(['new_password', 'new_password_confirmation'], new PresenceOf([
				'message' => [
					'new_password'              => 'password harus diisi',
					'new_password_confirmation' => 'konfirmasi password baru harus diisi',
				],
			]));
			$validator->add('new_password', new Confirmation([
				'with'    => 'new_password_confirmation',
				'message' => 'password pertama dan kedua harus sama',
			]));
		}
		if ($this->email) {
			$validator->add('email', new Email([
				'message' => 'email tidak valid',
			]));
			$validator->add('email', new Uniqueness([
				'convert' => function(array $values) : array {
					$values['email'] = strtolower($values['email']);
					return $values;
				},
				'message' => 'email sudah ada',
			]));
		}
		if ($this->date_of_birth) {
			$validator->add('date_of_birth', new Date([
				'format'  => 'Y-m-d',
				'message' => 'tanggal lahir tidak valid',
			]));
		}
		$validator->add('deposit', new Numericality([
			'message' => 'deposit harus dalam bentuk angka',
		]));
		if ($this->new_avatar) {
			$validator->add('new_avatar', new Callback([
				'callback' => function($data) use($max_size) {
					return $data->new_avatar->getSize() <= intval($max_size) * pow(1024, 2);
				},
				'message' => 'ukuran gambar maksimal ' . $max_size,
			]));
			$validator->add('new_avatar', new Callback([
				'callback' => function($data) {
					return in_array($data->new_avatar->getRealType(), ['image/jpeg', 'image/png']);
				},
				'message' => 'format gambar harus JPG atau PNG',
			]));
		}
		return $this->validate($validator);
	}

	function beforeSave() {
		$this->delivery_hours = implode(',', array_filter($this->delivery_hours)) ?: null;
		if ($this->new_avatar) {
			if (!$this->avatar) {
				$random = new Random;
				do {
					$this->avatar = $random->hex(16) . '.jpg';
					if (!is_readable($this->_upload_config->path . $this->avatar) && !static::findFirstByAvatar($this->avatar)) {
						break;
					}
				} while (1);
			} else {
				unlink($this->_upload_config->path . $this->avatar);
			}
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = implode(',', array_filter($this->thumbnails)) ?: null;
		if ($this->role_id != Role::MERCHANT) {
			$this->accumulation_divisor = 0;
		}
	}

	function afterSave() {
		$this->thumbnails     = array_filter(explode(',', $this->thumbnails));
		$this->delivery_hours = array_filter(explode(',', $this->delivery_hours));
		if ($this->new_avatar) {
			$avatar = $this->_upload_config->path . $this->avatar;
			$this->new_avatar->moveTo($avatar);
			$imagick = new Imagick($avatar);
			$imagick->setInterlaceScheme(Imagick::INTERLACE_PLANE);
			$imagick->writeImage($avatar);
		}
		if ($this->role_id == Role::MERCHANT) {
			$this->getDI()->getDb()->execute("UPDATE users SET keywords = TO_TSVECTOR('simple', company) WHERE id = {$this->id}");
		}
	}

	function beforeDelete() {
		if (!$this->avatar) {
			return;
		}
		$this->thumbnails[] = $this->avatar;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
	}

	function afterFetch() {
		$this->thumbnails     = array_filter(explode(',', $this->thumbnails));
		$this->delivery_hours = array_filter(explode(',', $this->delivery_hours));
	}

	function getThumbnail(int $width, int $height, string $default_avatar = null) {
		$avatar = $this->avatar ?? $default_avatar;
		if (!$avatar) {
			return null;
		}
		$thumbnail = strtr($avatar, ['.jpg' => $width . $height . '.jpg']);
		if (!in_array($thumbnail, $this->thumbnails)) {
			$imagick = new Imagick($this->_upload_config->path . $avatar);
			$imagick->cropThumbnailImage($width, $height);
			$imagick->setInterlaceScheme(Imagick::INTERLACE_PLANE);
			$imagick->writeImage($this->_upload_config->path . $thumbnail);
			if ($this->avatar) {
				$this->thumbnails[] = $thumbnail;
				$this->skipAttributes(['updated_by', 'updated_at']);
				$this->update();
			}
		}
		return $thumbnail;
	}

	function deleteAvatar() {
		$this->beforeDelete();
		$this->update(['avatar' => null, 'thumbnails' => null]);
	}

	function activate() {
		return $this->update([
			'status'           => array_search('ACTIVE', static::STATUS),
			'activation_token' => null,
			'activated_at'     => $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s'),
		]);
	}

	function suspend() {
		return $this->update([
			'status' => array_search('SUSPENDED', static::STATUS),
		]);
	}

	function reactivate() {
		return $this->update([
			'status' => array_search('ACTIVE', static::STATUS),
		]);
	}

	function businessDays() {
		$business_days = [
			$this->open_on_monday    ? 'Senin'  : ',',
			$this->open_on_tuesday   ? 'Selasa' : ',',
			$this->open_on_wednesday ? 'Rabu'   : ',',
			$this->open_on_thursday  ? 'Kamis'  : ',',
			$this->open_on_friday    ? 'Jumat'  : ',',
			$this->open_on_saturday  ? 'Sabtu'  : ',',
			$this->open_on_sunday    ? 'Minggu' : ',',
		];
		return trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-';
	}

	function deliveryHours() {
		$business_hours = range($this->business_opening_hour, $this->business_closing_hour);
		foreach ($business_hours as &$hour) {
			if (!in_array($hour, $this->delivery_hours)) {
				$hour = ',';
			} else {
				$hour .= '.00';
			}
		}
		$delivery_hours = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
		return $delivery_hours ? $delivery_hours . ' WIB' : '-';
	}

	function sendPasswordResetToken() {
		$random = new Random;
		do {
			$password_reset_token = $random->hex(3);
			if (!static::findFirstByPasswordResetToken($password_reset_token)) {
				break;
			}
		} while (1);
		$this->setPasswordResetToken($password_reset_token);
		$this->save();
		$sms = new Sms([
			'body'       => 'Token password Anda: ' . $password_reset_token,
			'created_by' => $this->id,
		]);
		return $sms->send([$this]);
	}

	function resetPassword($new_password) {
		$this->setNewPassword($new_password);
		$this->setNewPasswordConfirmation($new_password);
		$this->setPasswordResetToken(null);
		return $this->update();
	}

	function totalNewNotifications() {
		return $this->countNotifications('Application\Models\NotificationRecipient.read_at IS NULL');
	}

	function totalNewOrders() {
		return $this->role->name === 'Merchant' ? $this->countMerchantOrders('status = 0') : $this->countBuyerOrders('status = 0');
	}
}