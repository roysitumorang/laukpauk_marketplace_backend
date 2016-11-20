<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Image;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductPicture extends BaseModel {
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
			'alias'    => 'product',
			'reusable' => true,
		]);
	}

	function setProductId(int $product_id) {
		$this->parent_id = $product_id;
	}

	function setNewFile(array $new_file) {
		$this->new_file = $new_file;
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
	}

	function setPosition(int $position) {
		$this->position = $position;
	}

	function beforeValidationOnCreate() {
		do {
			$this->name = bin2hex(random_bytes(16)) . '.jpg';
			if (!is_readable($this->_upload_config->path . $this->name) && !static::findFirstByName($this->name)) {
				break;
			}
		} while (1);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('position', new PresenceOf([
			'message' => 'urutan harus diisi',
		]));
		$validator->add(['product_id', 'position'], new Uniqueness([
			'field'   => ['product_id', 'position'],
			'message' => 'urutan sudah ada',
		]));
		if ($this->new_file) {
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_file', new Image([
				'max_size'     => $max_size,
				'message_size' => 'ukuran file maksimal ' . $max_size,
				'message_type' => 'format gambar harus JPG atau PNG',
				'allowEmpty'   => true,
			]));
		}
		return $this->validate($validator);
	}

	function beforeUpdate() {
		if ($this->_newFileIsValid()) {
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = json_encode($this->thumbnails);
	}

	function save($data = null, $white_list = null) {
		return $this->_newFileIsValid() || ($this->id && $this->hasChanged('thumbnails')) ? parent::save($data, $white_list) : true;
	}

	function afterSave() {
		if (!$this->_newFileIsValid()) {
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

	private function _newFileIsValid() {
		return $this->new_file['tmp_name'] && !$this->new_file['error'] && $this->new_file['size'];
	}
}