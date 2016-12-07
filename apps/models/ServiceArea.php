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

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
		$this->belongsTo('village_id', 'Application\Models\Village', 'id', [
			'alias'    => 'village',
			'reusable' => true,
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