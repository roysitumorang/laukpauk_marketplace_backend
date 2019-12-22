<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\{Digit, Uniqueness};

class SalePackageProduct extends ModelBase {
	public $id;
	public $sale_package_id;
	public $user_product_id;
	public $quantity;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function initialize() {
		$this->setSource('sale_package_product');
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('sale_package_id', SalePackage::class, 'id', [
			'alias'      => 'salePackage',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'paket penjualan harus diisi',
			],
		]);
		$this->belongsTo('user_product_id', UserProduct::class, 'id', [
			'alias'      => 'userProduct',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'produk harus diisi',
			],
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['sale_package_id', 'user_product_id'], new Uniqueness([
			'message' => 'produk sudah ada dalam paket',
		]));
		$validator->add('quantity', new Digit([
			'message' => 'quantity harus diisi dalam bentuk angka',
		]));
		return $this->validate($validator);
	}
}