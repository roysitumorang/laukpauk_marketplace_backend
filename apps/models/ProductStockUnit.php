<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductStockUnit extends ModelBase {
	public $id;
	public $product_id;
	public $name;
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
		return 'product_stock_units';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'      => 'product',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->hasMany('id', 'Application\Models\ProductPrice', 'product_stock_unit_id', ['alias' => 'prices']);
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'satuan harus diisi',
		]));
		$validator->add(['product_id', 'name'], new Uniqueness([
			'message' => 'satuan sudah ada',
		]));
		return $this->validate($validator);
	}
}
