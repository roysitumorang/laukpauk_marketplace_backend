<?php

namespace Application\Models;

use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductDimension extends BaseModel {
	public $id;
	public $product_id;
	public $parameter;
	public $size;
	public $stock_keeping_unit;

	const STOCK_KEEPING_UNITS = ['cm', 'mm', 'liter', 'cl'];

	function getSource() {
		return 'product_dimensions';
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

	function setSize($size) {
		$this->size = $size;
	}

	function setStockKeepingUnit(string $stock_keeping_unit = null) {
		$this->stock_keeping_unit = $stock_keeping_unit ?? static::STOCK_KEEPING_UNITS[0];
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['parameter', 'size'], new PresenceOf([
			'message' => [
				'parameter' => 'parameter harus diisi',
				'size'      => 'ukuran harus diisi',
			],
		]));
		$validator->add(['product_id', 'parameter'], new Uniqueness([
			'model'   => $this,
			'message' => 'parameter sudah ada',
		]));
		$validator->add('size', new Numericality([
			'message' => 'ukuran dalam bentuk angka, desimal pake titik'
		]));
		$validator->add('stock_keeping_unit', new InclusionIn([
			'message' => 'satuan tidak valid',
			'domain'  => static::STOCK_KEEPING_UNITS,
		]));
		return $this->validate($validator);
	}
}