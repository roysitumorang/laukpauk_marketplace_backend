<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\File as FileValidator;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Url;

class Banner extends ModelBase {
	public $id;
	public $banner_category_id;
	public $name;
	public $link;
	public $type;
	public $published;
	public $file_url;
	public $file_name;
	public $new_file;
	public $thumbnails;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;
	private $_upload_config;

	const TYPES = ['Image'];

	function getSource() {
		return 'banners';
	}

	function onConstruct() {
		$this->_filter        = $this->getDI()->getFilter();
		$this->_upload_config = $this->getDI()->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('banner_category_id', 'Application\Models\BannerCategory', 'id', [
			'alias'      => 'category',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'id kategori harus diisi',
			],
		]);
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setLink(string $link) {
		if ($link) {
			$this->link = $link;
		}
	}

	function setType($type) {
		$this->type = $this->_filter->sanitize($type, 'int');
	}

	function setPublished($published) {
		$this->published = $published;
	}

	function setFileUrl($file_url) {
		if ($file_url) {
			$this->file_url = $file_url;
		}
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
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		if ($this->link) {
			$validator->add('link', new Url([
				'message' => 'link tidak valid',
			]));
		}
		if ($this->type) {
			$validator->add('type', new InclusionIn([
				'message' => 'link tidak valid',
				'domain'  =>  array_keys(static::TYPES),
			]));
		}
		if ($this->file_url) {
			$validator->add('file_url', new Url([
				'message' => 'file URL tidak valid',
			]));
		}
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

	function beforeSave() {
		if (!$this->new_file || $this->file_name) {
			return true;
		}
		$random = new Random;
		do {
			$this->file_name = $random->hex(16) . '.jpg';
			if (!static::findFirst(['conditions' => "file_name = '{$this->file_name}'"])) {
				break;
			}
		} while (1);
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

	function afterSave() {
		if (!$this->new_file) {
			return true;
		}
		$file_name = $this->_upload_config->path . $this->file_name;
		$gd      = new Gd($this->new_file['tmp_name']);
		$gd->save($file_name, 100);
		unlink($this->new_file['tmp_name']);
	}

	function beforeDelete() {
		$this->thumbnails[] = $this->file_name;
		foreach ($this->thumbnails as $thumbnail) {
			unlink($this->_upload_config->path . $thumbnail);
		}
	}

	function afterFetch() {
		$this->thumbnails = json_decode(trim(stripslashes($this->thumbnails), '"'));
	}

	function getThumbnail(int $width, int $height, string $default_picture = null) {
		$picture = $this->file_name ?? $default_picture;
		if (!$picture) {
			return null;
		}
		$thumbnail = str_replace('.jpg', $width . $height . '.jpg', $picture);
		if (!in_array($thumbnail, $this->thumbnails)) {
			$gd = new Gd($this->_upload_config->path . $picture);
			$gd->resize($width, $height);
			$gd->save($this->_upload_config->path . $thumbnail, 100);
			if ($this->file_name) {
				$this->thumbnails[] = $thumbnail;
				$this->skipAttributes(['updated_by', 'updated_at']);
				$this->update(['thumbnails' => $this->thumbnails]);
			}
		}
		return $thumbnail;
	}

	function deletePicture() {
		$this->beforeDelete();
		$this->file_name = null;
		$this->setThumbnails([]);
		$this->save();
	}
}