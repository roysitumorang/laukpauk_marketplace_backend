<?php

namespace Application\Models;

use Phalcon\Validation\Validator\Digit;
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

	function setProductCategoryId(int $product_category_id) {
		$this->product_category_id = $product_category_id;
	}

	function setCode(string $code = null) {
		$this->code = $code;
	}

	function setName(string $name) {
		$this->name = $name;
	}

	function setNewPermalink(string $new_permalink) {
		$this->new_permalink = $new_permalink;
	}

	function setDescription(string $description = null) {
		$this->description = $description;
	}

	function setPrice(int $price = 0) {
		$this->price = $price;
	}

	function setWeight(int $weight = 0) {
		$this->weight = $weight;
	}

	function setPublished(int $published = null) {
		$this->published = $published ?? 0;
	}

	function setStatus(int $status = null) {
		$this->status = isset(static::STATUS[$status]) ? $status : 0;
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

	function setStock(int $stock = 0) {
		$this->stock = $stock;
	}

	function setBrandId(int $brand_id = null) {
		$this->brand_id = $brand_id;
	}

	function setBuyPoint(int $buy_point = null) {
		$this->buy_point = $buy_point;
	}

	function setAffiliatePoint(int $affiliate_point = null) {
		$this->affiliate_point = $affiliate_point;
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
		$validator->add(['price', 'weight', 'stock'], new Digit([
			'message' => [
				'price'  => 'harga dalam bentuk angka',
				'weight' => 'berat dalam bentuk angka',
				'stock'  => 'stok dalam bentuk angka',
			],
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