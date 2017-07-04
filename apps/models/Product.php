<?php

namespace Application\Models;

use Imagick;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\File;
use Phalcon\Validation\Validator\InclusionIn;
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
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function getSource() {
		return 'products';
	}

	function onConstruct() {
		$di                   = $this->getDI();
		$this->_upload_config = $di->getConfig()->upload;
		$this->_filter        = $di->getFilter();
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
			'alias'    => 'user',
			'reusable' => true,
		]);
		$this->hasManyToMany('id', 'Application\Models\ProductGroupMember', 'product_id', 'product_group_id', 'Application\Models\ProductGroup', 'id', ['alias' => 'groups']);
		$this->hasManyToMany('id', 'Application\Models\UserProduct', 'product_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'merchants']);
		$this->hasManyToMany('id', 'Application\Models\OrderProduct', 'product_id', 'order_id', 'Application\Models\Order', 'id', ['alias' => 'orders']);
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

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int');
	}

	function thumbnail(int $width) {
		return '/assets/image/' . ($this->picture && in_array($width, static::THUMBNAIL_WIDTHS) ? str_replace('.jpg', $width . '.jpg', $this->picture) : 'no_picture_120.png');
	}

	function beforeValidation() {
		$this->published = $this->published ?? 0;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'stock_unit'], new PresenceOf([
			'message' => [
				'name'       => 'nama harus diisi',
				'stock_unit' => 'satuan harus diisi',
			],
		]));
		$validator->add(['user_id', 'product_category_id', 'name', 'stock_unit'], new Uniqueness([
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
		$validator->add('published', new InclusionIn([
			'message' => 'tampilkan antara 0 atau 1',
			'domain'  => [0, 1],
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
			$image   = new Imagick($this->new_picture['tmp_name']);
			$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
			$image->writeImage($picture);
			foreach (static::THUMBNAIL_WIDTHS as $width) {
				$file = str_replace('.jpg', $width . '.jpg', $this->picture);
				$path = $this->_upload_config->path . $file;
				in_array($file, $this->thumbnails) || $this->thumbnails[] = $file;
				$image = new Imagick($this->new_picture['tmp_name']);
				$image->thumbnailImage($width, 0);
				$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
				$image->writeImage($path);
			}
			unlink($this->new_picture['tmp_name']);
		}
		$this->thumbnails  = implode(',', array_filter($this->thumbnails)) ?: null;
		$this->description = $this->description ?: null;
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

	function afterSave() {
		$this->getDI()->getDb()->exec("UPDATE products a SET keywords = TO_TSVECTOR('simple', b.name || ' ' || a.name) FROM product_categories b WHERE a.product_category_id = b.id AND a.id = {$this->id}");
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