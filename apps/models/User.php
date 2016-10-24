<?php

namespace Application\Models;

use Application\Models\BaseModel;

class User extends BaseModel {
	const STATUS_HOLD   =  0;
	const STATUS_ACTIVE =  1;
	const STATUS_BANNED = -1;
	const GENDERS       = ['Pria', 'Wanita'];

	public $id;
	public $role_id;
	public $name;
	public $email;
	public $password;
	public $address;
	public $zip_code;
	public $subdistrict_id;
	public $phone;
	public $mobile;
	public $premium;
	public $affiliation_url;
	public $status;
	public $activated_at;
	public $activation_token;
	public $password_reset_token;
	public $last_seen;
	public $deposit;
	public $ktp;
	public $company;
	public $npwp;
	public $avatar;
	public $registration_ip;
	public $twitter_id;
	public $google_id;
	public $facebook_id;
	public $reward;
	public $gender;
	public $date_of_birth;
	public $buy_point;
	public $affiliation_point;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'users';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('role_id', 'Application\Models\Role', 'id');
	}

	function beforeCreate() {
		parent::beforeCreate();
		$this->status            = self::STATUS_HOLD;
		$this->activation_token  = bin2hex(random_bytes(32));
		$this->deposit           = 0;
		$this->reward            = 0;
		$this->buy_point         = 0;
		$this->affiliation_point = 0;
	}
}