<?php

namespace Application\Models;

use Imagick;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\{Between, Digit, File, PresenceOf, Uniqueness};

class SalePackage extends ModelBase {
	const THUMBNAIL_WIDTHS = [120, 300];

	public $id;
	public $user_id;
	public $name;
	public $price;
	public $stock;
	public $picture;
	public $new_picture;
	public $thumbnails;
	public $keywords;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'sale_packages';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'      => 'user',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'penjual harus diisi',
			],
		]);
		$this->hasManyToMany('id', 'Application\Models\SalePackageProduct', 'sale_package_id', 'user_product_id', 'Application\Models\UserProduct', 'id', ['alias' => 'userProducts']);
		$this->hasManyToMany('id', 'Application\Models\OrderProduct', 'sale_package_id', 'order_id', 'Application\Models\Order', 'id', ['alias' => 'orders']);
	}

	function setName(string $name) {
		$this->name = $name;
	}

	function setPrice(string $price) {
		$this->price = $price;
	}

	function setStock(string $stock) {
		$this->stock = $stock;
	}

	function setNewPicture($new_picture) {
		if (is_array($new_picture) && $new_picture['tmp_name'] && $new_picture['size'] && !$new_picture['error']) {
			$this->new_picture = $new_picture;
		}
	}

	function setPublished(string $published) {
		$this->published = $published;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'price', 'stock'], new PresenceOf([
			'message' => [
				'name'  => 'nama harus diisi',
				'price' => 'harga harus diisi',
				'stock' => 'stok harus diisi',
			]
		]));
		$validator->add(['price', 'stock'], new Digit([
			'message' => [
				'price' => 'harga dalam bentuk angka',
				'stock' => 'stok dalam bentuk angka',
			]
		]));
		$validator->add(['user_id', 'name'], new Uniqueness([
			'message' => 'nama sudah ada',
		]));
		$validator->add('published', new Between([
			'minimum' => 0,
			'maximum' => 1,
			'message' => 'tampilkan harus antara 0 and 1',
		]));
		if ($this->new_picture) {
			$max_size = $this->getDI()->getConfig()->upload->max_size;
			$validator->add('new_picture', new File([
				'maxSize'      => $max_size,
				'messageSize'  => 'ukuran file maksimal ' . $max_size,
				'allowedTypes' => ['image/jpeg', 'image/png'],
				'messageType'  => 'format gambar harus JPG atau PNG',
			]));
		}
		return $this->validate($validator);
	}

	function beforeSave() {
		$this->name     = implode(' ', preg_split('/\s/', $this->name, -1, PREG_SPLIT_NO_EMPTY));
		$this->keywords = $this->getDI()->getDb()->fetchColumn("SELECT TO_TSVECTOR('simple', '{$this->name}')");
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
			$image = new Imagick($this->new_picture['tmp_name']);
			$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
			foreach (static::THUMBNAIL_WIDTHS as $width) {
				$file = strtr($this->picture, ['.jpg' => $width . '.jpg']);
				in_array($file, $this->thumbnails) || $this->thumbnails[] = $file;
				$thumbnail = clone $image;
				$thumbnail->thumbnailImage($width, 0);
				$thumbnail->writeImage($this->getDI()->getConfig()->upload->path . $file);
			}
			$image->thumbnailImage(512, 0);
			$image->writeImage($this->getDI()->getConfig()->upload->path . $this->picture);
			unlink($this->new_picture['tmp_name']);
		}
		$this->thumbnails = implode(',', array_filter($this->thumbnails)) ?: null;
	}

	function beforeDelete() {
		if (!$this->picture) {
			return;
		}
		$this->thumbnails[] = $this->picture;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->getDI()->getConfig()->upload->path . $thumbnail);
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
			unlink($this->getDI()->getConfig()->upload->path . $thumbnail);
		}
		$this->picture = null;
		$this->thumbnails = null;
		$this->save();
	}
}