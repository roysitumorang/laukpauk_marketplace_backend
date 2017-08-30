<?php

namespace Application\Models;

use Phalcon\Image;
use Phalcon\Image\Adapter\Gd;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\File;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductCategory extends ModelBase {
	const THUMBNAIL_WIDTHS = [120, 300];

	public $id;
	public $name;
	public $permalink;
	public $new_permalink;
	public $picture;
	public $new_picture;
	public $thumbnails;
	public $published;
	public $description;
	public $meta_title;
	public $meta_keyword;
	public $meta_desc;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_upload_config;
	private $_filter;

	function getSource() {
		return 'product_categories';
	}

	function onConstruct() {
		$di                   = $this->getDI();
		$this->_upload_config = $di->getConfig()->upload;
		$this->_filter        = $di->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->hasMany('id', 'Application\Models\Product', 'product_category_id', ['alias' => 'products']);
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setNewPermalink(string $new_permalink) {
		$this->new_permalink = $new_permalink;
	}

	function setNewPicture(array $new_picture) {
		if ($new_picture['tmp_name'] && $new_picture['size'] && !$new_picture['error']) {
			$this->new_picture = $new_picture;
		}
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
	}

	function setPublished(int $published = null) {
		$this->published = $published ?? 0;
	}

	function setDescription(string $description) {
		$this->description = $this->_filter->sanitize($description, 'string');
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

	function thumbnail(int $width) {
		return '/assets/image/' . ($this->picture && in_array($width, static::THUMBNAIL_WIDTHS) ? str_replace('.jpg', $width . '.jpg', $this->picture) : 'no_picture_120.png');
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		$validator->add('name', new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		if (!$this->id || $this->new_permalink) {
			$validator->add('new_permalink', new Uniqueness([
				'attribute' => 'permalink',
				'message'   => 'permalink sudah ada',
			]));
		}
		if ($this->new_picture) {
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_picture', new File([
				'maxSize'      => $max_size,
				'messageSize'  => 'ukuran file maksimal ' . $max_size,
				'allowedTypes' => ['image/jpeg', 'image/png'],
				'messageType'  => 'format gambar harus JPG atau PNG',
			]));
		}
		return $this->validate($validator);
	}

	function beforeValidation() {
		$this->permalink = preg_replace(['/[^\w]/', '/_/', '/[-]+/'], '-', $this->new_permalink ? $this->_filter->sanitize($this->new_permalink, ['string', 'trim', 'lower']) : strtolower($this->name));
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
		$this->thumbnails = implode(',', array_filter($this->thumbnails)) ?: null;
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

	function getProducts($parameters = null) {
		return $this->getRelated('products', $parameters);
	}
}