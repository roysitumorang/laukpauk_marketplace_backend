<?php

namespace Application\Models;

use Application\Models\ModelBase;

class ProductGroup extends ModelBase {
	public $product_id;
	public $group_id;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'product_group';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('product_id', 'Application\Models\Product', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
		$this->belongsTo('group_id', 'Application\Models\Group', 'id', [
			'foreignKey' => ['allowNulls' => false],
		]);
	}
}