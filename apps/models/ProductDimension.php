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
	public $unit_of_measure;

	const UNIT_OF_MEASURES = ['cm', 'mm', 'liter', 'cl'];

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

	function setStockKeepingUnit(string $unit_of_measure = null) {
		$this->unit_of_measure = $unit_of_measure ?? static::UNIT_OF_MEASURES[0];
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
			'message' => 'parameter sudah ada',
		]));
		$validator->add('size', new Numericality([
			'message' => 'ukuran dalam bentuk angka, desimal pake titik'
		]));
		$validator->add('unit_of_measure', new InclusionIn([
			'message' => 'satuan tidak valid',
			'domain'  => static::UNIT_OF_MEASURES,
		]));
		return $this->validate($validator);
	}
}