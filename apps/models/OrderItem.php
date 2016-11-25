<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;

class OrderItem extends BaseModel {
	public $id;
	public $order_id;
	public $product_id;
	public $name;
	public $unit_price;
	public $unit_size;
	public $unit_of_measure;
	public $quantity;
	public $buy_point;
	public $affiliate_point;
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
			'alias'    => 'order',
			'reusable' => true,
		]);
	}

	function beforeValidationOnCreate() {
		$this->buy_point       = 0;
		$this->affiliate_point = 0;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'unit_price', 'unit_size', 'unit_of_measure', 'quantity'], new PresenceOf([
			'message' => [
				'name'            => 'nama produk harus diisi',
				'unit_price'      => 'harga satuan harus diisi',
				'unit_size'       => 'besar satuan harus diisi',
				'unit_of_measure' => 'satuan harus diisi',
				'quantity'        => 'jumlah harus diisi',
			],
		]));
		$validator->add(['unit_price', 'unit_size', 'quantity'], new Numericality([
			'message' => [
				'unit_price' => 'harga satuan harus dalam desimal',
				'unit_size'  => 'besar satuan harus dalam desimal',
				'quantity'   => 'jumlah harus dalam desimal',
			]
		]));
		return $this->validate($validator);
	}
}