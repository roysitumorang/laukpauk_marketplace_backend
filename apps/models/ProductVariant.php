<?php

namespace Application\Models;

use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductVariant extends ModelBase {
	public $id;
	public $product_id;
	public $parameter;
	public $value;
	public $stock;
	public $extra_price;
	public $published;

	const PARAMETERS = ['Ukuran', 'Warna', 'Model', 'Tipe', 'Bentuk', 'Rasa'];

	function getSource() {
		return 'product_variants';
	}

	function initialize() {
		parent::initialize();
		$this->skipAttributesOnUpdate(['product_id']);
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'    => 'product',
			'reusable' => true,
		]);
	}

	function setProductId(int $product_id) {
		$this->product_id = $product_id;
	}

	function setParameter(string $parameter) {
		$this->parameter = $parameter;
	}

	function setValue(string $value) {
		$this->value = $value;
	}

	function setPublished(int $published = null) {
		$this->published = $published ?? 1;
	}

	function setStock(int $stock = 0) {
		$this->stock = $stock;
	}

	function setExtraPrice(int $extra_price) {
		$this->extra_price = $extra_price;
	}

	function validation() {
		$validator = new Validation;
		$validator->add('parameter', new InclusionIn([
			'message' => 'paramter tidak valid',
			'domain'  => static::PARAMETERS,
		]));
		$validator->add(['value', 'stock', 'extra_price'], new PresenceOf([
			'message' => [
				'value'       => 'nama harus diisi',
				'stock'       => 'stok harus diisi',
				'extra_price' => 'tambahan harga harus diisi',
			],
		]));
		$validator->add(['stock', 'extra_price'], new Digit([
			'message' => [
				'stock'       => 'stok dalam bentuk angka',
				'extra_price' => 'tambahan harga dalam bentuk angka',
			],
		]));
		$validator->add(['product_id', 'parameter', 'value'], new Uniqueness([
			'message' => 'parameter dan nama sudah ada',
		]));
		return $this->validate($validator);
	}
}