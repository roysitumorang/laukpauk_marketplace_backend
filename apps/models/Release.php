<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Release extends ModelBase {
	const APPLICATION_TYPES = ['free', 'premium'];
	const USER_TYPES        = ['buyer', 'merchant'];

	public $id;
	public $application_type;
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

	function setApplicationType($application_type) {
		$this->application_type = $application_type;
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
		$validator->add(['application_type', 'user_type', 'version', 'features'], new PresenceOf([
			'message' => [
				'application_type' => 'tipe aplikasi harus diisi',
				'user_type'        => 'tipe user harus diisi',
				'version'          => 'versi harus diisi',
				'features'         => 'fitur harus diisi',
			],
		]));
		$validator->add(['application_type', 'user_type', 'version'], new Uniqueness([
			'convert' => function(array $values) : array {
				$values['version'] = strtolower($values['version']);
				return $values;
			},
			'message' => 'tipe aplikasi, tipe user dan versi sudah ada',
		]));
		$validator->add(['application_type', 'user_type'], new InclusionIn([
			'domain' => [
				'application_type' => static::APPLICATION_TYPES,
				'user_type'        => static::USER_TYPES,
			],
			'message' => [
				'application_type' => 'tipe aplikasi antara free dan premium',
				'user_type'        => 'tipe user antara buyer dan merchant',
			],
		]));
		return $this->validate($validator);
	}
}
