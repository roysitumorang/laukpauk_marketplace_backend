<?php

namespace Application\Models;

class ProductGroupMember extends ModelBase {
	public $product_group_id;
	public $product_id;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'product_group_member';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('product_group_id', ProductGroup::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('product_id', Product::class, 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}