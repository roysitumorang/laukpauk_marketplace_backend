<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class ProductLink extends ModelBase {
	public $id;
	public $product_id;
	public $linked_product_id;
	public $created_by;
	public $created_at;

	private $_filter;

	function getSource() {
		return 'product_links';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'alias'      => 'product',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('linked_product_id', 'Application\Models\Product', 'id', [
			'alias'      => 'linked_product',
			'reusable'   => true,
			'foreignKey' => ['allowNulls' => false],
		]);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['product_id', 'linked_product_id'], new Uniqueness([
			'message' => 'produk terkait sudah ada',
		]));
		return $this->validate($validator);
	}
}
