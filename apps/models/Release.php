<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Release extends ModelBase {
	const USER_TYPES = ['buyer', 'merchant'];

	public $id;
	public $user_type;
	public $version;
	public $features;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'releases';
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->hasMany('id', 'Application\Models\Coupon', 'release_id', ['alias' => 'releases']);
	}

	function setUserType($user_type) {
		$this->user_type = $user_type;
	}

	function setVersion($version) {
		$this->version = $version;
	}

	function setFeatures($features) {
		$this->features = $features;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['user_type', 'version', 'features'], new PresenceOf([
			'message' => [
				'user_type' => 'tipe user harus diisi',
				'version'   => 'versi harus diisi',
				'features'  => 'fitur harus diisi',
			],
		]));
		$validator->add(['user_type', 'version'], new Uniqueness([
			'convert' => function(array $values) : array {
				$values['version'] = strtolower($values['version']);
				return $values;
			},
			'message' => 'tipe user dan versi sudah ada',
		]));
		$validator->add('user_type', new InclusionIn([
			'domain'  => static::USER_TYPES,
			'message' => 'tipe user antara buyer dan merchant',
		]));
		return $this->validate($validator);
	}
}