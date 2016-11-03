<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Product extends BaseModel {
	public $id;
	public $product_category_id;
	public $code;
	public $name;
	public $permalink;
	public $new_permalink;
	public $description;
	public $price;
	public $weight;
	public $published;
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

	private $_filter;

	const STATUS = ['on call', 'available'];

	function getSource() {
		return 'products';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('product_category_id', 'Application\Models\ProductCategory', 'id', [
			'alias'    => 'category',
			'reusable' => true,
		]);
		$this->belongsTo('brand_id', 'Application\Models\Brand', 'id', [
			'alias'    => 'brand',
			'reusable' => true,
		]);
		$this->hasMany('id', 'Application\Models\ProductPicture', 'reference_id', ['alias' => 'pictures']);
		$this->hasMany('id', 'Application\Models\ProductVariant', 'reference_id', ['alias' => 'variants']);
		$this->hasMany('id', 'Application\Models\ProductDimension', 'reference_id', ['alias' => 'dimensions']);
	}

	function setCode($code) {
		$this->code = $this->_filter->sanitize($code, ['string', 'trim']);
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setStock($stock) {
		$this->stock = $this->_filter->sanitize($stock, 'int') ?: 0;
	}

	function setProductCategoryId($product_category_id) {
		$this->product_category_id = $this->_filter->sanitize($product_category_id, 'int');
	}

	function setBrandId($brand_id) {
		$this->brand_id = $this->_filter->sanitize($brand_id, 'int');
	}

	function setPrice($price) {
		$this->price = $this->_filter->sanitize($price, 'int') ?: 0;
	}

	function setWeight($weight) {
		$this->weight = $this->_filter->sanitize($weight, 'float') ?: 0.0;
	}

	function setDescription($description) {
		$this->description = $this->_filter->sanitize($description, 'string');
	}

	function setNewPermalink($new_permalink) {
		$this->new_permalink = $this->_filter->sanitize($new_permalink, 'string');
	}

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int');
	}

	function setStatus($status) {
		$this->status = $this->_filter->sanitize($status, 'int');
	}

	function setBuyPoint($buy_point) {
		$this->buy_point = $this->_filter->sanitize($buy_point, 'int') ?: 0;
	}

	function setAffiliatePoint($affiliate_point) {
		$this->affiliate_point = $this->_filter->sanitize($affiliate_point, 'int') ?: 0;
	}

	function setMetaTitle(string $meta_title) {
		$this->meta_title = $this->_filter->sanitize($meta_title ?: $this->name, ['string', 'trim']);
	}

	function setMetaKeyword(string $meta_keyword) {
		$this->meta_keyword = $this->_filter->sanitize($meta_keyword ?: $this->name, ['string', 'trim']);
	}

	function setMetaDesc(string $meta_desc) {
		$this->meta_desc = substr(str_replace(['\r', '\n'], ['', ' '], $this->_filter->sanitize($meta_desc, ['string', 'trim'])), 0, 160);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		$validator->add('name', new Uniqueness([
			'model'   => $this,
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		if ($this->code) {
			$validator->add('code', new Uniqueness([
				'model'   => $this,
				'convert' => function(array $values) : array {
					$values['code'] = strtolower($values['name']);
					return $values;
				},
				'message' => 'kode sudah ada',
			]));
		}
		$validator->add(['price', 'stock'], new Digit([
			'message' => [
				'price'  => 'harga harus diisi dalam bentuk angka',
				'stock'  => 'stok harus diisi dalam bentuk angka',
			],
		]));
		$validator->add('weight', new Numericality([
			'message' => 'berat harus diisi dalam bentuk desimal',
		]));
		if (!$this->id || $this->new_permalink) {
			$validator->add('new_permalink', new Uniqueness([
				'model'     => $this,
				'attribute' => 'permalink',
				'message'   => 'permalink sudah ada',
			]));
		}
		return $this->validate($validator);
	}

	function beforeValidation() {
		$this->permalink = preg_replace('/\s+/', '-', $this->new_permalink ? $this->_filter->sanitize($this->new_permalink, ['string', 'trim', 'lower']) : strtolower($this->name));
	}

	function beforeDelete() {
		foreach ($this->pictures as $picture) {
			$picture->delete();
		}
		foreach ($this->variants as $variant) {
			$variant->delete();
		}
		foreach ($this->dimensions as $dimension) {
			$dimension->delete();
		}
	}
}