<?php

namespace Application\Models;

use Application\Models\BaseModel;
use Phalcon\Text;

class User extends BaseModel {
	const STATUS = ['hold', 'active', 'banned'];
	const GENDERS = ['male', 'female'];
	const USER_TYPES = ['admin', 'shopper', 'affiliator'];

	public $id;
	public $username;
	public $name;
	public $email;
	public $password;
	public $verified_at;
	public $address;
	public $zip_code;
	public $city_id;
	public $phone;
	public $mobile;
	public $premium;
	public $affiliation_url;
	public $status;
	public $user_type;
	public $deposit;
	public $ktp;
	public $company;
	public $npwp;
	public $avatar;
	public $activation_ip;
	public $twitter_id;
	public $google_id;
	public $facebook_id;
	public $reward;
	public $gender;
	public $dob;
	public $buy_point;
	public $affiliation_point;
	public $remember_token;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'users';
	}

	function initialize() {
		parent::initialize();
		$this->hasMany('id', 'Application\Models\Message', 'user_id', [
			'alias'  => 'messages',
			'params' => [
				'order' => '[Application\Models\Message].id DESC',
			],
		]);
		$this->hasMany('id', 'Application\Models\Message', 'user_id', [
			'alias'  => 'unread_messages',
			'params' => [
				'conditions' => '[Application\Models\Message].updated_at IS NULL',
				'order'      => '[Application\Models\Message].id DESC',
			],
		]);
		$this->hasMany('id', 'Application\Models\Message', 'user_id', [
			'alias'  => 'read_messages',
			'params' => [
				'conditions' => '[Application\Models\Message].updated_at IS NOT NULL',
				'order'      => '[Application\Models\Message].id DESC',
			],
		]);
	}

	function beforeCreate() {
		parent::beforeCreate();
		$this->status           = 'hold';
		$this->activation_token = Text::random(Text::RANDOM_ALNUM, 32);
	}
}
