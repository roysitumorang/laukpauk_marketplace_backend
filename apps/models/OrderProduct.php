<?php

namespace Application\Models;

use Phalcon\Db;
use Phalcon\Validation;
use Phalcon\Validation\Validator\{Digit, PresenceOf};

class OrderProduct extends ModelBase {
	public $id;
	public $parent_id;
	public $order_id;
	public $sale_package_id;
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
		$this->belongsTo('parent_id', 'Application\Models\OrderProduct', 'id', [
			'alias'    => 'parent',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\OrderProduct', 'parent_id', [
			'alias' => 'children',
		]);
		$this->belongsTo('sale_package_id', 'Application\Models\SalePackage', 'id', [
			'alias'    => 'salePackage',
			'reusable' => true,
		]);
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'    => 'product',
			'reusable' => true,
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

	function afterCreate() {
		if (!$this->sale_package_id) {
			return;
		}
		$result = $this->getDI()->getDb()->query(<<<QUERY
			SELECT
				c.id,
				c.name,
				c.stock_unit,
				b.price,
				b.published,
				a.quantity
			FROM
				sale_package_product a
				JOIN user_product b ON a.user_product_id = b.id
				JOIN products c ON b.product_id = c.id
			WHERE
				a.sale_package_id = {$this->sale_package_id}
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$order_product = new static;
			$order_product->create([
				'parent_id'       => $this->id,
				'order_id'        => $this->order_id,
				'sale_package_id' => null,
				'product_id'      => $item->id,
				'name'            => $item->name,
				'price'           => $item->price,
				'stock_unit'      => $item->stock_unit,
				'quantity'        => $item->quantity * $this->quantity,
				'created_by'      => $this->created_by,
				'created_at'      => $this->created_at,
			]);
		}
	}
}