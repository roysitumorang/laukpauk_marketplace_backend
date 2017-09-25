<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class SalePackageProduct extends ModelBase {
	public $id;
	public $sale_package_id;
	public $user_product_id;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'sale_package_product';
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('sale_package_id', 'Application\Models\SalePackage', 'id', [
			'alias'      => 'salePackage',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'paket penjualan harus diisi',
			],
		]);
		$this->belongsTo('user_product_id', 'Application\Models\UserProduct', 'id', [
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
		return $this->validate($validator);
	}
}