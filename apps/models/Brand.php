<?php

namespace Application\Models;

class Brand extends BaseModel {
	public $id;
	public $name;
	public $permalink;
	public $description;
	public $meta_title;
	public $meta_desc;
	public $meta_keyword;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'brands';
	}

	function initialize() {
		parent::initialize();
		$this->hasMany('id', 'Application\Models\Product', 'brand_id', ['alias' => 'products']);
		$this->hasMany('id', 'Application\Models\BrandPicture', 'reference_id', ['alias' => 'pictures']);
	}
}