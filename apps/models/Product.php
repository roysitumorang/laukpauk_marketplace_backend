<?php

namespace Application\Models;

use Phalcon\Image;
use Phalcon\Image\Adapter\Gd;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\File;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Product extends ModelBase {
	const THUMBNAIL_WIDTHS = [120, 300];

	public $id;
	public $user_id;
	public $product_category_id;
	public $name;
	public $description;
	public $stock_unit;
	public $lifetime;
	public $picture;
	public $new_picture;
	public $thumbnails;
	public $price;
	public $stock;
	public $published;
	public $order_closing_hour;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function getSource() {
		return 'products';
	}

	function onConstruct() {
		$this->_upload_config = $this->getDI()->getConfig()->upload;
		$this->_filter        = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('product_category_id', 'Application\Models\ProductCategory', 'id', [
			'alias'      => 'category',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'kategori harus diisi',
			],
		]);
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'      => 'merchant',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'merchant harus diisi',
			],
		]);
		$this->hasManyToMany('id', 'Application\Models\ProductLink', 'product_id', 'linked_product_id', 'Application\Models\Product', 'id', ['alias' => 'linked_products']);
		$this->hasManyToMany('id', 'Application\Models\ProductLink', 'linked_product_id', 'product_id', 'Application\Models\Product', 'id', ['alias' => 'linkers']);
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setDescription($description) {
		if ($description) {
			$this->description = $this->_filter->sanitize($description, ['string', 'trim']);
		}
	}

	function setStockUnit($stock_unit) {
		$this->stock_unit = $this->_filter->sanitize($stock_unit, ['string', 'trim']);
	}

	function setLifetime($lifetime) {
		$this->lifetime = $this->_filter->sanitize($lifetime, 'int') ?: null;
	}

	function setNewPicture($new_picture) {
		if (is_array($new_picture) && $new_picture['tmp_name'] && $new_picture['size'] && !$new_picture['error']) {
			$this->new_picture = $new_picture;
		}
	}

	function setPrice($price) {
		$this->price = $this->_filter->sanitize($price, 'int') ?: 0;
	}

	function setStock($stock) {
		$this->stock = $this->_filter->sanitize($stock, 'int') ?: 0;
	}

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int');
	}

	function setOrderClosingHour($order_closing_hour) {
		$this->order_closing_hour = $this->_filter->sanitize($order_closing_hour, ['string', 'trim']) ?: null;
	}

	function thumbnail(int $width) {
		return '/assets/image/' . ($this->picture && in_array($width, static::THUMBNAIL_WIDTHS) ? str_replace('.jpg', $width . '.jpg', $this->picture) : 'no_picture_120.png');
	}

	function beforeValidation() {
		$this->published = $this->published ?? 0;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'stock_unit', 'price', 'stock'], new PresenceOf([
			'message' => [
				'name'       => 'nama harus diisi',
				'stock_unit' => 'satuan harus diisi',
				'price'      => 'harga harus diisi',
				'stock'      => 'stok harus diisi',
			],
		]));
		$validator->add(['name', 'stock_unit', 'product_category_id', 'user_id'], new Uniqueness([
			'message' => 'nama, satuan dan kategori sudah ada',
		]));
		if ($this->new_picture) {
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_picture', new File([
				'maxSize'      => $max_size,
				'messageSize'  => 'ukuran file maksimal ' . $max_size,
				'allowedTypes' => ['image/jpeg', 'image/png'],
				'messageType'  => 'format gambar harus JPG atau PNG',
			]));
		}
		$validator->add(['price', 'stock'], new Digit([
			'message' => [
				'price' => 'harga harus dalam bentuk angka',
				'stock' => 'stok harus dalam bentuk angka',
			],
		]));

		return $this->validate($validator);
	}

	function beforeSave() {
		if ($this->new_picture) {
			if (!$this->picture) {
				$random = new Random;
				do {
					$this->picture = $random->hex(16) . '.jpg';
					if (!static::findFirstByPicture($this->picture)) {
						break;
					}
				} while (1);
			}
			$picture = $this->_upload_config->path . $this->picture;
			$gd      = new Gd($this->new_picture['tmp_name']);
			imageinterlace($gd->getImage(), 1);
			$gd->save($picture, 100);
			foreach (static::THUMBNAIL_WIDTHS as $width) {
				$file = str_replace('.jpg', $width . '.jpg', $this->picture);
				$path = $this->_upload_config->path . $file;
				in_array($file, $this->thumbnails) || $this->thumbnails[] = $file;
				$gd = new Gd($this->new_picture['tmp_name']);
				imageinterlace($gd->getImage(), 1);
				$gd->resize($width, null, Image::WIDTH);
				$gd->save($path, 100);
			}
			unlink($this->new_picture['tmp_name']);
		}
		$this->thumbnails  = implode(',', array_filter($this->thumbnails)) ?: null;
		$this->description = $this->description ?: null;
		if (!$this->price) {
			$this->published = 0;
		}
	}

	function beforeDelete() {
		if (!$this->picture) {
			return;
		}
		$this->thumbnails[] = $this->picture;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
	}

	function afterFetch() {
		$this->thumbnails = explode(',', $this->thumbnails);
	}

	function deletePicture() {
		if (!$this->picture) {
			return;
		}
		$this->thumbnails[] = $this->picture;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
		$this->picture = null;
		$this->thumbnails = null;
		$this->save();
	}
}