<?php

namespace Application\Models;

Use Imagick;
use Phalcon\Http\Request\File;
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

	function setNewFile(File $new_file) {
		if ($new_file->getTempName() && $new_file->getSize() && !$new_file->getError()) {
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
					return $data->new_file instanceof File && $data->new_file->getTempName() && is_uploaded_file($data->new_file->getTempName());
				},
				'message' => 'gambar harus diisi',
			]));
			$validator->add('new_file', new Callback([
				'callback' => function($data) use($max_size) {
					return $data->new_file instanceof File && $data->new_file->getSize() <= intval($max_size) * pow(1024, 2);
				},
				'message' => 'ukuran gambar maksimal ' . $max_size,
			]));
			$validator->add('new_file', new Callback([
				'callback' => function($data) {
					return $data->new_file instanceof File && in_array($data->new_file->getRealType(), ['image/jpeg', 'image/png']);
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
		$this->thumbnails = implode(',', array_filter($this->thumbnails)) ?: null;
	}

	function afterSave() {
		$this->thumbnails = array_filter(explode(',', $this->thumbnails));
		if (!$this->new_file) {
			return true;
		}
		$file = $this->_upload_config->path . $this->file;
		$this->new_file->moveTo($file);
		$image = new Imagick($file);
		$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
		$image->writeImage($file);
	}

	function beforeDelete() {
		$this->thumbnails[] = $this->file;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
	}

	function afterFetch() {
		$this->thumbnails = array_filter(explode(',', $this->thumbnails));
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
			$this->update();
		}
		return $thumbnail;
	}
}