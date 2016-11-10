<?php

namespace Application\Models;

use Application\Models\BaseModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class User extends BaseModel {
	const STATUS      = [
		'HOLD'      =>  0,
		'ACTIVE'    =>  1,
		'SUSPENDED' => -1,
	];
	const GENDERS     = ['Pria', 'Wanita'];
	const MEMBERSHIPS = ['Free', 'Premium'];

	public $id;
	public $role_id;
	public $name;
	public $email;
	public $password;
	public $new_password;
	public $new_password_confirmation;
	public $address;
	public $zip_code;
	public $subdistrict_id;
	public $phone;
	public $mobile;
	public $premium;
	public $affiliate_link;
	public $status;
	public $activated_at;
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
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'users';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('role_id', 'Application\Models\Role', 'id', [
			'alias'    => 'role',
			'reusable' => true,
		]);
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setEmail($email) {
		$this->email = $this->_filter->sanitize($email, ['string', 'trim']);
	}

	function setNewPassword($new_password) {
		$this->new_password = $this->_filter->sanitize($new_password, ['string', 'trim']);
	}

	function setNewPasswordConfirmation($new_password_confirmation) {
		$this->new_password_confirmation = $this->_filter->sanitize($new_password_confirmation, ['string', 'trim']);
	}

	function setAddress($address) {
		$this->address = $this->_filter->sanitize($address, ['string', 'trim']);
	}

	function setZipCode($zip_code) {
		$this->zip_code = $this->_filter->sanitize($zip_code, 'int');
	}

	function setSubdistrictId($subdistrict_id) {
		$this->subdistrict_id = $this->_filter->sanitize($subdistrict_id, 'int');
	}

	function setPhone($phone) {
		$this->phone = $this->_filter->sanitize($phone, 'int');
	}

	function setMobile($mobile) {
		$this->mobile = $this->_filter->sanitize($mobile, 'int');
	}

	function setPremium($premium) {
		$this->premium = $this->_filter->sanitize($premium, 'int');
	}

	function setAffiliateLink($affiliate_link) {
		$this->affiliate_link = $this->_filter->sanitize($affiliate_link, ['string', 'trim']);
	}

	function setStatus($status) {
		$this->status = $this->_filter->sanitize($status, 'int');
	}

	function setActivatedAt($activated_at) {
		$this->activated_at = $this->_filter->sanitize($activated_at, ['string', 'trim']);
	}

	function setActivationToken($activation_token) {
		$this->activation_token = $this->_filter->sanitize($activation_token, 'alphanum');
	}

	function setPasswordResetToken($password_reset_token) {
		$this->password_reset_token = $this->_filter->sanitize($password_reset_token, 'alphanum');
	}

	function setLastSeen($last_seen) {
		$this->last_seen = $this->_filter->sanitize($last_seen, ['string', 'trim']);
	}

	function setDeposit($deposit) {
		$this->deposit = $this->_filter->sanitize($deposit, 'int') ?? 0;
	}

	function setKtp($ktp) {
		$this->ktp = $this->_filter->sanitize($ktp, ['string', 'trim']);
	}

	function setCompany($company) {
		$this->company = $this->_filter->sanitize($company, ['string', 'trim']);
	}

	function setNpwp($npwp) {
		$this->npwp = $this->_filter->sanitize($npwp, ['string', 'trim']);
	}

	function setRegistrationIp($registration_ip) {
		$this->registration_ip = $this->_filter->sanitize($registration_ip, ['string', 'trim']);
	}

	function setTwitterId($twitter_id) {
		$this->twitter_id = $this->_filter->sanitize($twitter_id, 'int');
	}

	function setGoogleId($google_id) {
		$this->google_id = $this->_filter->sanitize($google_id, 'int');
	}

	function setFacebookId($facebook_id) {
		$this->facebook_id = $this->_filter->sanitize($facebook_id, 'int');
	}

	function setReward($reward) {
		$this->reward = $this->_filter->sanitize($reward, 'int') ?? 0;
	}

	function setGender($gender) {
		$this->gender = $this->_filter->sanitize($gender, 'alphanum');
	}

	function setDateOfBirth($date_of_birth) {
		$this->date_of_birth = $this->_filter->sanitize($date_of_birth, ['string', 'trim']);
	}

	function setBuyPoint($buy_point) {
		$this->buy_point = $this->_filter->sanitize($buy_point, 'int') ?? 0;
	}

	function setAffiliatePoint($affiliate_point) {
		$this->affiliate_point = $this->_filter->sanitize($affiliate_point, 'int') ?? 0;
	}

	function beforeValidationOnCreate() {
		$this->status           = static::STATUS['HOLD'];
		$this->registration_ip  = $this->getDI()->getRequest()->getClientAddress();
		$this->password         = password_hash($this->new_password, PASSWORD_DEFAULT);
		do {
			$this->activation_token = bin2hex(random_bytes(32));
			if (!static::findByActivationToken($this->activation_token)) {
				break;
			}
		} while (1);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'email', 'phone', 'deposit', 'reward', 'buy_point', 'affiliate_point'], new PresenceOf([
			'message' => [
				'name'            => 'nama harus diisi',
				'email'           => 'email harus diisi',
				'phone'           => 'phone number harus diisi',
				'deposit'         => 'deposit harus diisi',
				'reward'          => 'reward harus diisi',
				'buy_point'       => 'poin buy harus diisi',
				'affiliate_point' => 'poin affiliasi harus diisi',
			],
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
		$validator->add('email', new Email([
			'message' => 'email tidak valid',
		]));
		$validator->add('email', new Uniqueness([
			'model'   => $this,
			'convert' => function(array $values) : array {
				$values['email'] = strtolower($values['email']);
				return $values;
			},
			'message' => 'email sudah ada',
		]));
		$validator->add(['deposit', 'reward', 'buy_point', 'affiliate_point'], new Digit([
			'message' => [
				'deposit'         => 'deposit harus dalam bentuk angka',
				'reward'          => 'reward harus dalam bentuk angka',
				'buy_point'       => 'poin buy harus dalam bentuk angka',
				'affiliate_point' => 'poin affiliasi harus dalam bentuk angka',
			],
		]));
		if ($this->zip_code) {
			$validator->add('zip_code', new Digit([
				'message' => 'post code harus dalam bentuk 5 angka',
			]));
		}
		return $this->validate($validator);
	}
}