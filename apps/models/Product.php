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
	public $lifetime;
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
		$this->hasManyToMany('id', 'Application\Models\StoreItem', 'product_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'merchants']);
		$this->hasManyToMany('id', 'Application\Models\ProductLink', 'product_id', 'linked_product_id', 'Application\Models\Product', 'id', ['alias' => 'linked_products']);
		$this->hasManyToMany('id', 'Application\Models\ProductLink', 'linked_product_id', 'product_id', 'Application\Models\Product', 'id', ['alias' => 'linkers']);
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

	function setLifetime($lifetime) {
		$this->lifetime = $this->_filter->sanitize($lifetime, 'int') ?: null;
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