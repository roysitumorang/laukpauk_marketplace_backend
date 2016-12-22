<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Product extends ModelBase {
	public $id;
	public $product_category_id;
	public $name;
	public $description;
	public $stock_unit;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function getSource() {
		return 'products';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('product_category_id', 'Application\Models\ProductCategory', 'id', [
			'alias'      => 'category',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'kategori harus diisi',
			],
		]);
		$this->hasMany('id', 'Application\Models\ProductPicture', 'product_id', ['alias' => 'pictures']);
		$this->hasMany('id', 'Application\Models\ProductPrice', 'product_id', ['alias' => 'prices']);
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setDescription($description) {
		if ($description) {
			$this->description = $this->_filter->sanitize($description, ['string', 'trim']);
		}
	}

	function setStockUnit($stock_unit) {
		$this->stock_unit = $this->_filter->sanitize($stock_unit, ['string', 'trim']);
	}

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int');
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'stock_unit'], new PresenceOf([
			'message' => [
				'name'       => 'nama harus diisi',
				'stock_unit' => 'satuan harus diisi',
			],
		]));
		$validator->add('name', new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		$validator->add(['name', 'stock_unit'], new Uniqueness([
			'message' => 'nama dan satuan sudah ada',
		]));
		return $this->validate($validator);
	}

	function beforeDelete() {
		foreach ($this->pictures as $picture) {
			$picture->delete();
		}
	}
}