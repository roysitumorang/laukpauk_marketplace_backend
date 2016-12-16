<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Date;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\File as FileValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class User extends ModelBase {
	const STATUS        = [
		0  => 'HOLD',
		1  => 'ACTIVE',
		-1 => 'SUSPENDED',
	];
	const GENDERS       = ['Pria', 'Wanita'];
	const MEMBERSHIPS   = ['Free', 'Premium'];
	const BUSINESS_DAYS = [
		'Minggu',
		'Senin',
		'Selasa',
		'Rabu',
		'Kamis',
		'Jumat',
		'Sabtu',
	];

	public $id;
	public $role_id;
	public $name;
	public $email;
	public $password;
	public $new_password;
	public $new_password_confirmation;
	public $address;
	public $village_id;
	public $phone;
	public $mobile;
	public $premium;
	public $affiliate_link;
	public $status;
	public $activated_at;
	public $verified_at;
	public $activation_token;
	public $password_reset_token;
	public $last_seen;
	public $deposit;
	public $ktp;
	public $company;
	public $npwp;
	public $registration_ip;
	public $twitter_id;
	public $google_id;
	public $facebook_id;
	public $reward;
	public $gender;
	public $date_of_birth;
	public $buy_point;
	public $affiliate_point;
	public $avatar;
	public $new_avatar;
	public $thumbnails;
	public $business_days;
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
		$this->hasMany('id', 'Application\Models\LoginHistory', 'user_id', ['alias' => 'login_history']);
		$this->hasMany('id', 'Application\Models\Order', 'buyer_id', ['alias' => 'buyer_orders']);
		$this->hasMany('id', 'Application\Models\Order', 'merchant_id', ['alias' => 'merchant_orders']);
		$this->hasMany('id', 'Application\Models\ProductPrice', 'user_id', ['alias' => 'product_prices']);
		$this->hasMany('id', 'Application\Models\ServiceArea', 'user_id', ['alias' => 'service_areas']);
		$this->hasManyToMany('id', 'Application\Models\MessageRecipient', 'user_id', 'message_id', 'Application\Models\Message', 'id', ['alias' => 'messages']);
		$this->hasMany('id', 'Application\Models\Notification', 'user_id', ['alias' => 'notifications']);
		$this->hasMany('id', 'Application\Models\AccessToken', 'user_id', ['alias' => 'access_tokens']);
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

	function setAddress($address) {
		if ($address) {
			$this->address = $this->_filter->sanitize($address, ['string', 'trim']);
		}
	}

	function setPhone($phone) {
		$this->phone = $this->_filter->sanitize($phone, 'int');
	}

	function setMobile($mobile) {
		if ($mobile) {
			$this->mobile = $this->_filter->sanitize($mobile, 'int');
		}
	}

	function setPremium($premium) {
		$this->premium = $this->_filter->sanitize($premium, 'int');
	}

	function setAffiliateLink($affiliate_link) {
		if ($affiliate_link) {
			$this->affiliate_link = $this->_filter->sanitize($affiliate_link, ['string', 'trim']);
		}
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

	function setLastSeen($last_seen) {
		if ($last_seen) {
			$this->last_seen = $this->_filter->sanitize($last_seen, ['string', 'trim']);
		}
	}

	function setDeposit($deposit) {
		$this->deposit = $this->_filter->sanitize($deposit, 'int') ?? 0;
	}

	function setKtp($ktp) {
		if ($ktp) {
			$this->ktp = $this->_filter->sanitize($ktp, ['string', 'trim']);
		}
	}

	function setCompany($company) {
		if ($company) {
			$this->company = $this->_filter->sanitize($company, ['string', 'trim']);
		}
	}

	function setNpwp($npwp) {
		if ($npwp) {
			$this->npwp = $this->_filter->sanitize($npwp, ['string', 'trim']);
		}
	}

	function setTwitterId($twitter_id) {
		if ($twitter_id) {
			$this->twitter_id = $this->_filter->sanitize($twitter_id, 'int');
		}
	}

	function setGoogleId($google_id) {
		if ($google_id) {
			$this->google_id = $this->_filter->sanitize($google_id, 'int');
		}
	}

	function setFacebookId($facebook_id) {
		if ($facebook_id) {
			$this->facebook_id = $this->_filter->sanitize($facebook_id, 'int');
		}
	}

	function setReward($reward) {
		$this->reward = $this->_filter->sanitize($reward, 'int') ?? 0;
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

	function setBuyPoint($buy_point) {
		$this->buy_point = $this->_filter->sanitize($buy_point, 'int') ?? 0;
	}

	function setAffiliatePoint($affiliate_point) {
		$this->affiliate_point = $this->_filter->sanitize($affiliate_point, 'int') ?? 0;
	}

	function setNewAvatar(array $new_avatar) {
		if ($new_avatar['tmp_name'] && $new_avatar['size'] && !$new_avatar['error']) {
			$this->new_avatar = $new_avatar;
		}
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
	}

	function setBusinessDays(array $business_days = null) {
		if ($business_days) {
			$this->business_days = $business_days;
		}
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
		$random                 = new Random;
		$this->status           = array_search('HOLD', static::STATUS);
		$this->registration_ip  = $this->getDI()->getRequest()->getClientAddress();
		$this->activation_token = $random->hex(16);
	}

	function beforeValidation() {
		$this->role_id = $this->role->id;
		if (!$this->id || $this->new_password) {
			$this->password = password_hash($this->new_password, PASSWORD_DEFAULT);
		}
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'phone', 'deposit', 'reward', 'buy_point', 'affiliate_point'], new PresenceOf([
			'message' => [
				'name'            => 'nama harus diisi',
				'phone'           => 'nomor HP harus diisi',
				'deposit'         => 'deposit harus diisi',
				'reward'          => 'reward harus diisi',
				'buy_point'       => 'poin buy harus diisi',
				'affiliate_point' => 'poin affiliasi harus diisi',
			],
		]));
		$validator->add('phone', new Uniqueness([
			'message' => 'no telepon sudah ada',
		]));
		if (!$this->id || $this->new_password || $this->new_password_confirmation) {
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
		$validator->add(['deposit', 'reward', 'buy_point', 'affiliate_point'], new Digit([
			'message' => [
				'deposit'         => 'deposit harus dalam bentuk angka',
				'reward'          => 'reward harus dalam bentuk angka',
				'buy_point'       => 'poin buy harus dalam bentuk angka',
				'affiliate_point' => 'poin affiliasi harus dalam bentuk angka',
			],
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
		$this->business_days = $this->business_days ? json_encode($this->business_days, JSON_NUMERIC_CHECK) : null;
		if ($this->_new_avatar && !$this->avatar) {
			$random = new Random;
			do {
				$this->avatar = $random->hex(16) . '.jpg';
				if (!static::findFirstByAvatar($this->avatar)) {
					break;
				}
			} while (1);
		}
	}

	function beforeUpdate() {
		parent::beforeUpdate();
		if ($this->new_avatar) {
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = json_encode($this->thumbnails);
	}

	function afterSave() {
		if (!$this->new_avatar) {
			return true;
		}
		$avatar = $this->_upload_config->path . $this->avatar;
		$gd     = new Gd($this->new_avatar['tmp_name']);
		$gd->save($avatar, 100);
		unlink($this->new_avatar['tmp_name']);
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
		$this->thumbnails    = json_decode($this->thumbnails);
		$this->business_days = $this->business_days ? json_decode($this->business_days) : [];
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