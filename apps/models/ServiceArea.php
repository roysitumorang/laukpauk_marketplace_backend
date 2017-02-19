<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ServiceArea extends ModelBase {
	public $id;
	public $user_id;
	public $village_id;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'service_areas';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'      => 'user',
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('village_id', 'Application\Models\Village', 'id', [
			'alias'      => 'village',
			'foreignKey' => ['allowNulls' => false],
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['user_id', 'village_id'], new PresenceOf([
			'message' => [
				'user_id'    => 'penjual harus diisi',
				'village_id' => 'kelurahan harus diisi',
			]
		]));
		$validator->add(['user_id', 'village_id'], new Uniqueness([
			'message' => 'kelurahan sudah ada',
		]));
		return $this->validate($validator);
	}
}