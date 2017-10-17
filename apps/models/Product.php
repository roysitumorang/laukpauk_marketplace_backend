<?php

namespace Application\Models;

use Imagick;
use Phalcon\Http\Request\File;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\{Callback, InclusionIn, PresenceOf, StringLength, Uniqueness};

class Product extends ModelBase {
	const THUMBNAIL_WIDTHS = [120, 300];

	public $id;
	public $product_category_id;
	public $name;
	public $description;
	public $stock_unit;
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

	function setNewPicture(File $new_picture) {
		if ($new_picture->getTempName() && $new_picture->getSize() && !$new_picture->getError()) {
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
		$validator->add('stock_unit', new StringLength([
			'min'            => 1,
			'max'            => 10,
			'messageMinimum' => 'satuan minimal 1 karakter',
			'messageMaximum' => 'satuan maksimal 10 karakter',
		]));
		$validator->add(['product_category_id', 'name', 'stock_unit'], new Uniqueness([
			'message' => 'nama, satuan dan kategori sudah ada',
		]));
		if ($this->new_picture) {
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_picture', new Callback([
				'callback' => function($data) use($max_size) {
					return $data->new_picture->getSize() <= intval($max_size) * pow(1024, 2);
				},
				'message' => 'ukuran gambar maksimal ' . $max_size,
			]));
			$validator->add('new_picture', new Callback([
				'callback' => function($data) {
					return in_array($data->new_picture->getRealType(), ['image/jpeg', 'image/png']);
				},
				'message' => 'format gambar harus JPG atau PNG',
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
					if (!is_readable($this->_upload_config->path . $this->picture) && !static::findFirstByPicture($this->picture)) {
						break;
					}
				} while (1);
			}
			$picture = $this->_upload_config->path . $this->picture;
			$this->new_picture->moveTo($picture);
			$image = new Imagick($picture);
			$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
			foreach (static::THUMBNAIL_WIDTHS as $width) {
				$file = strtr($this->picture, ['.jpg' => $width . '.jpg']);
				in_array($file, $this->thumbnails) || $this->thumbnails[] = $file;
				$thumbnail = clone $image;
				$thumbnail->thumbnailImage($width, 0);
				$thumbnail->writeImage($this->_upload_config->path . $file);
			}
			$image->thumbnailImage(512, 0);
			$image->writeImage($picture);
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
		$this->thumbnails = array_filter(explode(',', $this->thumbnails));
	}

	function afterSave() {
		$this->thumbnails = array_filter(explode(',', $this->thumbnails));
		$this->getDI()->getDb()->execute("UPDATE products a SET keywords = TO_TSVECTOR('simple', b.name || ' ' || a.name) FROM product_categories b WHERE a.product_category_id = b.id AND a.id = {$this->id}");
	}

	function deletePicture() {
		if (!$this->picture) {
			return;
		}
		$this->thumbnails[] = $this->picture;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
		$this->save(['picture' => null, 'thumbnails' => null]);
	}
}