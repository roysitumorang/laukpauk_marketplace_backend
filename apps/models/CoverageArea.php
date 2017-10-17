<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\{Callback, PresenceOf, Uniqueness};

class CoverageArea extends ModelBase {
	public $id;
	public $user_id;
	public $village_id;
	public $shipping_cost;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'coverage_area';
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

	function setShippingCost($shipping_cost) {
		$this->shipping_cost = $this->getDI()->getFilter()->sanitize($shipping_cost, 'int', 0);
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
		$validator->add('shipping_cost', new Callback([
			'callback' => function($data) {
				return filter_var($data->shipping_cost, FILTER_VALIDATE_INT) !== false && $data->shipping_cost >= 0;
			},
			'message'  => 'ongkos kirim harus diisi angka, minimal 0',
		]));
		return $this->validate($validator);
	}
}