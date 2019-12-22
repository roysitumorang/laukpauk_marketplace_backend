<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\{Digit, InclusionIn, PresenceOf, Uniqueness};

class UserProduct extends ModelBase {
	public $id;
	public $user_id;
	public $product_id;
	public $price;
	public $stock;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function onConstruct() {
		$di                   = $this->getDI();
		$this->_upload_config = $di->getConfig()->upload;
		$this->_filter        = $di->getFilter();
	}

	function initialize() {
		$this->setSource('user_product');
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('user_id', User::class, 'id', [
			'alias'      => 'user',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'merchant harus diisi',
			],
		]);
		$this->belongsTo('product_id', Product::class, 'id', [
			'alias'      => 'product',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'produk harus diisi',
			],
		]);
		$this->hasManyToMany('id', SalePackageProduct::class, 'user_product_id', 'sale_package_id', SalePackage::class, 'id', ['alias' => 'salePackages']);
	}

	function setPrice($price) {
		$this->price = $this->_filter->sanitize($price, 'int') ?: 0;
	}

	function setStock($stock) {
		$this->stock = $this->_filter->sanitize($stock, 'int') ?: 0;
	}

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int');
	}

	function beforeValidation() {
		$this->published = $this->published ?? 0;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['price', 'stock'], new PresenceOf([
			'message' => [
				'price' => 'harga harus diisi',
				'stock' => 'stok harus diisi',
			],
		]));
		$validator->add(['user_id', 'product_id'], new Uniqueness([
			'message' => 'produk sudah ada',
		]));
		$validator->add(['price', 'stock'], new Digit([
			'message' => [
				'price' => 'harga harus dalam bentuk angka',
				'stock' => 'stok harus dalam bentuk angka',
			],
		]));
		$validator->add('published', new InclusionIn([
			'message' => 'tampilkan antara 0 atau 1',
			'domain'  => [0, 1],
		]));
		return $this->validate($validator);
	}

	function beforeSave() {
		if (!$this->price) {
			$this->published = 0;
		}
	}
}