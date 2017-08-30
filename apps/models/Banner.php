<?php

namespace Application\Models;

Use Imagick;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class Banner extends ModelBase {
	public $id;
	public $published;
	public $file;
	public $new_file;
	public $thumbnails;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;
	private $_upload_config;

	function getSource() {
		return 'banners';
	}

	function onConstruct() {
		$di                   = $this->getDI();
		$this->_filter        = $di->getFilter();
		$this->_upload_config = $di->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
	}

	function setPublished($published) {
		$this->published = $published;
	}

	function setNewFile(array $new_file) {
		if ($new_file['tmp_name'] && $new_file['size'] && !$new_file['error']) {
			$this->new_file = $new_file;
		}
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
	}

	function validation() {
		$validator = new Validation;
		$max_size  = $this->_upload_config->max_size;
		if (!$this->id || $this->new_file) {
			$validator->add('new_file', new Callback([
				'callback' => function($data) {
					return $data->new_file['tmp_name'] && is_uploaded_file($data->new_file['tmp_name']);
				},
				'message' => 'gambar harus diisi',
			]));
			$validator->add('new_file', new Callback([
				'callback' => function($data) use($max_size) {
					return filesize($data->new_file['tmp_name']) <= intval($max_size) * pow(1024, 2);
				},
				'message' => 'ukuran gambar maksimal ' . $max_size,
			]));
			$validator->add('new_file', new Callback([
				'callback' => function($data) {
					return in_array(mime_content_type($data->new_file['tmp_name']), ['image/jpeg', 'image/png']);
				},
				'message' => 'format gambar harus JPG atau PNG',
			]));
		}
		return $this->validate($validator);
	}

	function beforeCreate() {
		$random = new Random;
		do {
			$this->file = $random->hex(16) . '.jpg';
			if (!static::findFirstByFile($this->file)) {
				break;
			}
		} while (1);
	}

	function beforeUpdate() {
		if ($this->new_file) {
			$this->thumbnails[] = $this->file;
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = json_encode($this->thumbnails);
	}

	function afterSave() {
		if (!$this->new_file) {
			return true;
		}
		$file  = $this->_upload_config->path . $this->file;
		$image = new Imagick($this->new_file['tmp_name']);
		$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
		$image->writeImage($file);
		unlink($this->new_file['tmp_name']);
	}

	function beforeDelete() {
		$this->thumbnails[] = $this->file;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
	}

	function afterFetch() {
		$this->thumbnails = $this->thumbnails ? json_decode($this->thumbnails) : [];
	}

	function getThumbnail(int $width, int $height) {
		$thumbnail = strtr($this->file, ['.jpg' => $width . $height . '.jpg']);
		if (!in_array($thumbnail, $this->thumbnails)) {
			$image = new Imagick($this->_upload_config->path . $this->file);
			$image->thumbnailImage($width, $height);
			$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
			$image->writeImage($this->_upload_config->path . $thumbnail);
			$this->thumbnails[] = $thumbnail;
			$this->skipAttributes(['updated_by', 'updated_at']);
			$this->update(['thumbnails' => $this->thumbnails]);
		}
		return $thumbnail;
	}
}