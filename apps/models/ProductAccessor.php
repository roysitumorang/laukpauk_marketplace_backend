<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class ProductAccessor extends ModelBase {
	public $id;
	public $product_id;
	public $user_id;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'product_accessors';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'      => 'user',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'      => 'product',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => false],
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['user_id', 'product_id'], new Uniqueness([
			'message' => 'produk sudah ada',
		]));
		return $this->validate($validator);
	}
}
