<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class StoreItem extends ModelBase {
	const MAX_PRICE = 500000;
	const MAX_STOCK = 1000;

	public $id;
	public $user_id;
	public $product_id;
	public $price;
	public $stock;
	public $published;
	public $order_closing_hour;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function getSource() {
		return 'store_items';
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

	function setPrice($price) {
		$this->price = $this->_filter->sanitize($price, 'int') ?: 0;
	}

	function setStock($stock) {
		$this->stock = $this->_filter->sanitize($stock, 'int') ?: 0;
	}

	function setPublished($published) {
		$this->published = $published == 1 ? 1 : 0;
	}

	function setOrderClosingHour($order_closing_hour) {
		$this->order_closing_hour = $this->_filter->sanitize($order_closing_hour, ['string', 'trim']) ?: null;
	}

	function beforeValidation() {
		$this->published = $this->published ?? 0;
	}

	function beforeSave() {
		if (!$this->price) {
			$this->published = 0;
		}
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['price', 'stock'], new PresenceOf([
			'message' => [
				'price' => 'harga harus diisi',
				'stock' => 'stok harus diisi',
			],
		]));
		$validator->add(['price', 'stock'], new Digit([
			'message' => [
				'price' => 'harga harus dalam bentuk angka',
				'stock' => 'stok harus dalam bentuk angka',
			],
		]));
		$validator->add(['price', 'stock'], new Between([
			'minimum' => [
				'price' => 0,
				'stock' => 0,
			],
			'maximum' => [
				'price' => static::MAX_PRICE,
				'stock' => static::MAX_STOCK,
			],
			'message' => [
				'price' => 'harga minimal 0, maksimal ' . number_format(static::MAX_PRICE, 0, ',', '.'),
				'stock' => 'stok minimal 0, maksimal ' . number_format(static::MAX_STOCK, 0, ',', '.'),
			],
		]));
		$validator->add(['user_id', 'product_id'], new Uniqueness([
			'message' => 'produk sudah ada',
		]));
		return $this->validate($validator);
	}
}