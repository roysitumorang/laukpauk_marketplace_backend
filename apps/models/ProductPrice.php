<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductPrice extends BaseModel {
	public $id;
	public $user_id;
	public $product_id;
	public $value;
	public $unit_size;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	const SIZES = [
		'1.0'    => '1',
		'0.5'  => '1/2',
		'0.25' => '1/4',
	];

	function getSource() {
		return 'product_prices';
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
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'    => 'product',
			'reusable' => true,
		]);
	}

	function setValue($value) {
		$this->value = $this->_filter->sanitize($value, 'int') ?: null;
	}

	function setUnitSize($unit_size) {
		$this->unit_size = $this->_filter->sanitize($unit_size, 'float') ?: 1;
	}

	function beforeValidation() {
		$this->published = $this->published ?? 0;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['value', 'unit_size'], new PresenceOf([
			'message' => [
				'value'     => 'harga harus diisi',
				'unit_size' => 'jumlah satuan harus diisi',
			]
		]));
		$validator->add('value', new Digit([
			'message' => 'harga harus diisi dalam bentuk angka',
		]));
		$validator->add('unit_size', new InclusionIn([
			'domain'  => array_keys(static::SIZES),
			'message' => 'jumlah satuan harus diantara 1/4, 1/2 atau 1',
		]));
		$validator->add(['user_id', 'product_id', 'unit_size'], new Uniqueness([
			'message' => 'jumlah satuan sudah ada',
		]));
		return $this->validate($validator);
	}

	function publish() {
		return $this->update(['published' => 1]);
	}

	function unpublish() {
		return $this->update(['published' => 0]);
	}
}