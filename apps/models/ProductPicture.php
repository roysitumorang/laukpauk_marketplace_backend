<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\File as FileValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductPicture extends ModelBase {
	public $id;
	public $product_id;
	public $name;
	public $new_file;
	public $thumbnails;
	public $position;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_upload_config;

	function getSource() {
		return 'product_pictures';
	}

	function onConstruct() {
		$this->_upload_config = $this->getDI()->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->skipAttributesOnUpdate(['product_id']);
		$this->keepSnapshots(true);
		$this->belongsTo('product_id', 'Product', 'id', [
			'alias'      => 'product',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'id produk harus diisi',
			],
		]);
	}

	function setNewFile(array $new_file) {
		if ($new_file['tmp_name'] && $new_file['size'] && !$new_file['error']) {
			$this->new_file = $new_file;
		}
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
	}

	function setPosition($position) {
		$this->position = $position ?? 0;
	}

	function beforeValidationOnCreate() {
		$random = new Random;
		do {
			$this->name = $random->hex(16) . '.jpg';
			if (!static::findFirstByName($this->name)) {
				break;
			}
		} while (1);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('position', new PresenceOf([
			'message' => 'urutan harus diisi',
		]));
		$validator->add('position', new Digit([
			'message' => 'posisi harus dalam bentuk angka',
		]));
		$validator->add(['product_id', 'position'], new Uniqueness([
			'field'   => ['product_id', 'position'],
			'message' => 'urutan sudah ada',
		]));
		if ($this->new_file) {
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_file', new FileValidator([
				'maxSize'      => $max_size,
				'messageSize'  => 'ukuran file maksimal ' . $max_size,
				'allowedTypes' => ['image/jpeg', 'image/png'],
				'messageType'  => 'format gambar harus JPG atau PNG',
			]));
		}
		return $this->validate($validator);
	}

	function beforeUpdate() {
		if ($this->new_file) {
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = json_encode($this->thumbnails);
	}

	function save($data = null, $white_list = null) {
		return $this->new_file || ($this->id && $this->hasChanged('thumbnails')) ? parent::save($data, $white_list) : true;
	}

	function afterSave() {
		if ($this->new_file) {
			return true;
		}
		$file = $this->_upload_config->path . $this->name;
		$gd   = new Gd($this->new_file['tmp_name']);
		$gd->save($file, 100);
		unlink($this->new_file['tmp_name']);
	}

	function beforeDelete() {
		$this->thumbnails[] = $this->name;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
	}

	function afterFetch() {
		$this->thumbnails = json_decode($this->thumbnails);
	}

	function getThumbnail(int $width, int $height, string $default_file = null) {
		$file = $this->name ?? $default_file;
		if (!$this->id || !$file) {
			return null;
		}
		$thumbnail = str_replace('.jpg', $width . $height . '.jpg', $file);
		if (!in_array($thumbnail, $this->thumbnails)) {
			$gd = new Gd($this->_upload_config->path . $file);
			$gd->resize($width, $height);
			$gd->save($this->_upload_config->path . $thumbnail, 100);
			$this->thumbnails[] = $thumbnail;
			$this->setThumbnails($this->thumbnails);
			$this->skipAttributes(['updated_by', 'updated_at']);
			$this->update();
		}
		return $thumbnail;
	}
}