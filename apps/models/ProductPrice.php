<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductPrice extends ModelBase {
	public $id;
	public $user_id;
	public $product_id;
	public $value;
	public $unit_size;
	public $published;
	public $order_closing_hour;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	const SIZES = [
		'1.0'  => 'satu',
		'0.5'  => 'setengah',
		'0.25' => 'seperempat',
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
			'alias'      => 'user',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'      => 'product',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => false],
		]);
	}

	function setValue($value) {
		$this->value = $this->_filter->sanitize($value, 'int') ?: null;
	}

	function setUnitSize($unit_size) {
		$this->unit_size = $this->_filter->sanitize($unit_size, 'float') ?: 1;
	}

	function setOrderClosingHour($order_closing_hour) {
		if ($order_closing_hour) {
			$this->order_closing_hour = $order_closing_hour;
		}
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
			'message' => 'produk sudah ada',
		]));
		return $this->validate($validator);
	}
}