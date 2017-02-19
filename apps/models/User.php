<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Date;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\File as FileValidator;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class User extends ModelBase {
	const STATUS = [
		0  => 'HOLD',
		1  => 'ACTIVE',
		-1 => 'SUSPENDED',
	];
	const GENDERS        = ['Pria', 'Wanita'];
	const BUSINESS_HOURS = [
		'opening' => 6,
		'closing' => 18,
	];

	public $id;
	public $role_id;
	public $api_key;
	public $premium_merchant;
	public $merchant_id;
	public $merchant_token;
	public $domain;
	public $minimum_purchase;
	public $admin_fee;
	public $name;
	public $email;
	public $password;
	public $new_password;
	public $new_password_confirmation;
	public $change_password;
	public $address;
	public $village_id;
	public $mobile_phone;
	public $status;
	public $activated_at;
	public $verified_at;
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
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'users';
	}

	function onConstruct() {
		$this->_upload_config = $this->getDI()->getConfig()->upload;
		$this->_filter        = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('role_id', 'Application\Models\Role', 'id', [
			'alias'      => 'role',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'role harus diisi',
			],
		]);
		$this->belongsTo('village_id', 'Application\Models\Village', 'id', [
			'alias'      => 'village',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'kelurahan harus diisi',
			],
		]);
		$this->belongsTo('merchant_id', 'Application\Models\User', 'id', [
			'alias'    => 'merchant',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\LoginHistory', 'user_id', ['alias' => 'login_history']);
		$this->hasMany('id', 'Application\Models\Order', 'buyer_id', ['alias' => 'buyer_orders']);
		$this->hasMany('id', 'Application\Models\Order', 'merchant_id', ['alias' => 'merchant_orders']);
		$this->hasManyToMany('id', 'Application\Models\StoreItem', 'user_id', 'product_id', 'Application\Models\Product', 'id', ['alias' => 'products']);
		$this->hasManyToMany('id', 'Application\Models\ServiceArea', 'user_id', 'village_id', 'Application\Models\Village', 'id', ['alias' => 'service_areas']);
		$this->hasManyToMany('id', 'Application\Models\MessageRecipient', 'user_id', 'message_id', 'Application\Models\Message', 'id', ['alias' => 'messages']);
		$this->hasManyToMany('id', 'Application\Models\NotificationRecipient', 'user_id', 'notification_id', 'Application\Models\Notification', 'id', ['alias' => 'notifications']);
		$this->hasMany('id', 'Application\Models\Device', 'user_id', ['alias' => 'devices']);
		$this->hasManyToMany('id', 'Application\Models\CouponUser', 'user_id', 'coupon_id', 'Application\Models\Coupon', 'id', ['alias' => 'users']);
	}

	function setPremiumMerchant($premium_merchant) {
		$this->premium_merchant = $this->_filter->sanitize($premium_merchant, 'int') ?: null;
	}

	function setMerchantId($merchant_id) {
		$this->merchant_id = $this->_filter->sanitize($merchant_id, 'int') ?: null;
	}

	function setDomain($domain) {
		$this->domain = $this->_filter->sanitize($domain, ['string', 'trim']) ?: null;
	}

	function setMinimumPurchase($minimum_purchase) {
		$this->minimum_purchase = filter_var($minimum_purchase, FILTER_VALIDATE_INT) ?: null;
	}

	function setAdminFee($admin_fee) {
		$this->admin_fee = filter_var($admin_fee, FILTER_VALIDATE_INT) ?: null;
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

	function setStatus($status) {
		$this->status = $this->_filter->sanitize($status, 'int');
	}

	function setActivatedAt($activated_at) {
		if ($activated_at) {
			$this->activated_at = $this->_filter->sanitize($activated_at, ['string', 'trim']);
		}
	}

	function setVerifiedAt($verified_at) {
		if ($verified_at) {
			$this->verified_at = $this->_filter->sanitize($verified_at, ['string', 'trim']);
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

	function setNewAvatar(array $new_avatar) {
		if ($new_avatar['tmp_name'] && $new_avatar['size'] && !$new_avatar['error']) {
			$this->new_avatar = $new_avatar;
		}
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
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
		if ($this->role_id == Role::MERCHANT && $this->premium_merchant) {
			do {
				$this->merchant_token = $random->hex(16);
				if (!static::findFirstByMerchantToken($this->merchant_token)) {
					break;
				}
			} while (1);
		}
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
		if ($this->role->id != Role::MERCHANT) {
			$this->premium_merchant = null;
			$this->minimal_purchase = null;
			$this->admin_fee        = null;
			$this->domain           = null;
		}
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'mobile_phone', 'deposit', 'address'], new PresenceOf([
			'message' => [
				'name'         => 'nama harus diisi',
				'mobile_phone' => 'nomor HP harus diisi',
				'deposit'      => 'deposit harus diisi',
				'address'      => 'alamat harus diisi',
			],
		]));
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
			if ($this->minimum_purchase) {
				$validator->add('minimum_purchase', new Digit([
					'message' => 'minimal order harus dalam bentuk angka',
				]));
				$validator->add('minimum_purchase', new Between([
					'minimum' => 0,
					'maximum' => 100000,
					'message' => 'minimal order paling sedikit 0, maksimal ' . number_format(100000, 0, ',', '.'),
				]));
			}
			if ($this->admin_fee) {
				$validator->add('admin_fee', new Digit([
					'message' => 'biaya administrasi harus dalam bentuk angka',
				]));
				$validator->add('admin_fee', new Between([
					'minimum' => 0,
					'maximum' => 10000,
					'message' => 'biaya administrasi minimal 0, maksimal ' . number_format(10000, 0, ',', '.'),
				]));
			}
			if ($this->domain) {
				$validator->add('domain', new Uniqueness([
					'convert' => function(array $values) : array {
						$values['domain'] = strtolower($values['domain']);
						return $values;
					},
					'message' => 'domain sudah ada',
				]));
			}
		}
		if ($this->getSnapshotData()['mobile_phone'] != $this->mobile_phone) {
			$validator->add(['mobile_phone', 'merchant_id'], new Uniqueness([
				'message' => 'nomor HP sudah ada',
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
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_avatar', new FileValidator([
				'maxSize'      => $max_size,
				'messageSize'  => 'ukuran file maksimal ' . $max_size,
				'allowedTypes' => ['image/jpeg', 'image/png'],
				'messageType'  => 'format gambar harus JPG atau PNG',
			]));
		}
		return $this->validate($validator);
	}

	function beforeSave() {
		if ($this->new_avatar && !$this->avatar) {
			$random = new Random;
			do {
				$this->avatar = $random->hex(16) . '.jpg';
				if (!is_readable($this->_upload_config->path . $this->avatar) && !static::findFirstByAvatar($this->avatar)) {
					break;
				}
			} while (1);
		}
	}

	function beforeUpdate() {
		if ($this->new_avatar) {
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = $this->thumbnails ? json_encode($this->thumbnails) : null;
	}

	function afterSave() {
		$this->thumbnails = $this->thumbnails ? json_decode($this->thumbnails) : [];
		if ($this->new_avatar) {
			$avatar = $this->_upload_config->path . $this->avatar;
			$gd     = new Gd($this->new_avatar['tmp_name']);
			$gd->save($avatar, 100);
			unlink($this->new_avatar['tmp_name']);
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
		$this->thumbnails = $this->thumbnails ? json_decode($this->thumbnails) : [];
	}

	function getThumbnail(int $width, int $height, string $default_avatar = null) {
		$avatar = $this->avatar ?? $default_avatar;
		if (!$avatar) {
			return null;
		}
		$thumbnail = str_replace('.jpg', $width . $height . '.jpg', $avatar);
		if (!in_array($thumbnail, $this->thumbnails)) {
			$gd = new Gd($this->_upload_config->path . $avatar);
			$gd->resize($width, $height);
			$gd->save($this->_upload_config->path . $thumbnail, 100);
			if ($this->avatar) {
				$this->thumbnails[] = $thumbnail;
				$this->setThumbnails($this->thumbnails);
				$this->skipAttributes(['updated_by', 'updated_at']);
				$this->update();
			}
		}
		return $thumbnail;
	}

	function deleteAvatar() {
		$this->beforeDelete();
		$this->avatar = null;
		$this->setThumbnails([]);
		$this->save();
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
}