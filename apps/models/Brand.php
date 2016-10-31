<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Image;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Brand extends BaseModel {
	public $id;
	public $name;
	public $permalink;
	public $new_permalink;
	public $picture;
	public $new_picture;
	public $thumbnails;
	public $description;
	public $meta_title;
	public $meta_desc;
	public $meta_keyword;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_upload_config;

	function getSource() {
		return 'brands';
	}

	function onConstruct() {
		$this->_filter        = $this->getDI()->getFilter();
		$this->_upload_config = $this->getDI()->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->hasMany('id', 'Application\Models\Product', 'brand_id', ['alias' => 'products']);
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setNewPermalink(string $new_permalink) {
		$this->new_permalink = $new_permalink;
	}

	function setNewPicture(array $new_picture) {
		$this->new_picture = $new_picture;
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
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
		if (!$this->id || $this->new_permalink) {
			$validator->add('new_permalink', new Uniqueness([
				'model'     => $this,
				'attribute' => 'permalink',
				'message'   => 'permalink sudah ada',
			]));
		}
		if ($this->new_picture) {
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_picture', new Image([
				'max_size'     => $max_size,
				'message_size' => 'ukuran file maksimal ' . $max_size,
				'message_type' => 'format gambar harus JPG atau PNG',
				'allowEmpty'   => true,
			]));
		}
		return $this->validate($validator);
	}

	function beforeValidation() {
		$this->permalink = preg_replace('/\s+/', '-', $this->new_permalink ? $this->_filter->sanitize($this->new_permalink, ['string', 'trim', 'lower']) : strtolower($this->name));
	}

	function beforeSave() {
		if (!$this->_newPictureIsValid() || $this->picture) {
			return true;
		}
		do {
			$this->picture = bin2hex(random_bytes(16)) . '.jpg';
			if (!is_readable($this->_upload_config->path . $this->picture) && !static::findFirstByPicture($this->picture)) {
				break;
			}
		} while (1);
	}

	function beforeUpdate() {
		parent::beforeUpdate();
		if ($this->_newPictureIsValid()) {
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = json_encode($this->thumbnails);
	}

	function afterSave() {
		if (!$this->_newPictureIsValid()) {
			return true;
		}
		$picture = $this->_upload_config->path . $this->picture;
		$gd      = new Gd($this->new_picture['tmp_name']);
		$gd->save($picture, 100);
		unlink($this->new_picture['tmp_name']);
	}

	function beforeDelete() {
		$this->thumbnails[] = $this->picture;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
		foreach ($this->products as $product) {
			$product->save(['brand_id' => null]);
		}
	}

	function afterFetch() {
		$this->thumbnails = json_decode($this->thumbnails);
	}

	function getThumbnail(int $width, int $height, string $default_picture = null) {
		$picture = $this->picture ?? $default_picture;
		if (!$picture) {
			return null;
		}
		$thumbnail = str_replace('.jpg', $width . $height . '.jpg', $picture);
		if (!in_array($thumbnail, $this->thumbnails)) {
			$gd = new Gd($this->_upload_config->path . $picture);
			$gd->resize($width, $height);
			$gd->save($this->_upload_config->path . $thumbnail, 100);
			if ($this->picture) {
				$this->thumbnails[] = $thumbnail;
				$this->setThumbnails($this->thumbnails);
				$this->skipAttributes(['updated_by', 'updated_at']);
				$this->save();
			}
		}
		return $thumbnail;
	}

	function deletePicture() {
		$this->beforeDelete();
		$this->picture = null;
		$this->setThumbnails([]);
		$this->save();
	}

	private function _newPictureIsValid() {
		return $this->new_picture['tmp_name'] && !$this->new_picture['error'] && $this->new_picture['size'];
	}
}