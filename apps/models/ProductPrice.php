<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductPrice extends ModelBase {
	public $id;
	public $user_id;
	public $product_id;
	public $value;
	public $published;
	public $order_closing_hour;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

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
		$validator->add('value', new PresenceOf([
			'message' => 'harga harus diisi',
		]));
		$validator->add('value', new Digit([
			'message' => 'harga harus diisi dalam bentuk angka',
		]));
		$validator->add(['user_id', 'product_id'], new Uniqueness([
			'message' => 'produk sudah ada',
		]));
		return $this->validate($validator);
	}
}