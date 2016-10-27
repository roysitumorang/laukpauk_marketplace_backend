<?php

namespace Application\Models;

class Product extends BaseModel {
	public $id;
	public $product_category_id;
	public $code;
	public $name;
	public $permalink;
	public $description;
	public $price;
	public $weight;
	public $show;
	public $status;
	public $meta_title;
	public $meta_desc;
	public $meta_keyword;
	public $stock;
	public $brand_id;
	public $buy_point;
	public $affiliate_point;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'products';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('product_category_id', 'Application\Models\ProductCategory', 'id', ['alias' => 'category']);
		$this->belongsTo('brand_id', 'Application\Models\Brand', 'id', ['alias' => 'brand']);
		$this->hasMany('id', 'Application\Models\ProductPicture', 'reference_id', ['alias' => 'pictures']);
	}
}