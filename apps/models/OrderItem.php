<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\PresenceOf;

class OrderItem extends ModelBase {
	public $id;
	public $order_id;
	public $product_id;
	public $name;
	public $unit_price;
	public $stock_unit;
	public $quantity;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'order_items';
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
		$validator->add(['name', 'unit_price', 'stock_unit', 'quantity'], new PresenceOf([
			'message' => [
				'name'       => 'nama produk harus diisi',
				'unit_price' => 'harga satuan harus diisi',
				'stock_unit' => 'satuan harus diisi',
				'quantity'   => 'jumlah harus diisi',
			],
		]));
		$validator->add(['unit_price', 'quantity'], new Digit([
			'message' => [
				'unit_price' => 'harga satuan harus dalam angka',
				'quantity'   => 'jumlah harus dalam angka',
			],
		]));
		$validator->add(['unit_price', 'quantity'], new Between([
			'minimum' => [
				'unit_price' => 1,
				'quantity'   => 1,
			],
			'message' => [
				'unit_price' => 'harga satuan minimal 1',
				'quantity'   => 'jumlah minimal 1',
			],
		]));
		return $this->validate($validator);
	}
}