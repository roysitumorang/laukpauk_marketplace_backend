<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\PresenceOf;

class OrderProduct extends ModelBase {
	public $id;
	public $order_id;
	public $product_id;
	public $name;
	public $price;
	public $stock_unit;
	public $quantity;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'order_product';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('order_id', 'Application\Models\Order', 'id', [
			'alias'      => 'order',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'order tidak ditemukan',
			],
		]);
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'      => 'product',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'produk tidak ditemukan',
			],
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'price', 'stock_unit', 'quantity'], new PresenceOf([
			'message' => [
				'name'       => 'nama produk harus diisi',
				'price'      => 'harga satuan harus diisi',
				'stock_unit' => 'satuan harus diisi',
				'quantity'   => 'jumlah harus diisi',
			],
		]));
		$validator->add(['price', 'quantity'], new Digit([
			'message' => [
				'price'    => 'harga satuan harus dalam angka',
				'quantity' => 'jumlah harus dalam angka',
			],
		]));
		return $this->validate($validator);
	}
}