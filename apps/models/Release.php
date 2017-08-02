<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Release extends ModelBase {
	const TYPES = ['buyer', 'merchant'];

	public $id;
	public $type;
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

	function setType(string $type) {
		$this->type = $type;
	}

	function setVersion(string $version) {
		$this->version = $version;
	}

	function setFeatures(string $features) {
		$this->features = $features;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['type', 'version', 'features'], new PresenceOf([
			'message' => [
				'type'     => 'tipe harus diisi',
				'version'  => 'versi harus diisi',
				'features' => 'fitur harus diisi',
			],
		]));
		$validator->add('version', new Uniqueness([
			'convert' => function(array $values) : array {
				$values['version'] = strtolower($values['version']);
				return $values;
			},
			'message' => 'versi sudah ada',
		]));
		$validator->add('type', new InclusionIn([
			'domain'  => static::TYPES,
			'message' => 'tipe antara buyer atau merchant',
		]));
		return $this->validate($validator);
	}
}