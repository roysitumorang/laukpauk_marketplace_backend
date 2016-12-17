<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\File as FileValidator;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Url;

class Page extends ModelBase {
	public $id;
	public $page_category_id;
	public $parent_id;
	public $name;
	public $permalink;
	public $new_permalink;
	public $url;
	public $body;
	public $url_target;
	public $picture;
	public $new_picture;
	public $thumbnails;
	public $meta_title;
	public $meta_desc;
	public $meta_keyword;
	public $published;
	public $position;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;
	private $_upload_config;

	const URL_TARGETS = [
		'_self',
		'_blank',
		'_parent',
	];

	function getSource() {
		return 'pages';
	}

	function onConstruct() {
		$this->_filter        = $this->getDI()->getFilter();
		$this->_upload_config = $this->getDI()->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->hasMany('id', 'Application\Models\Page', 'parent_id', [
			'alias'      => 'sub_pages',
			'foreignKey' => [
				'message' => 'page tidak dapat dihapus karena memiliki sub page',
			],
		]);
		$this->belongsTo('page_category_id', 'Application\Models\PageCategory', 'id', [
			'alias'    => 'category',
			'reusable' => true,
		]);
		$this->belongsTo('parent_id', 'Application\Models\Page', 'id', [
			'alias'    => 'parent',
			'reusable' => true,
		]);
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setNewPermalink($new_permalink) {
		if ($new_permalink) {
			$this->new_permalink = $new_permalink;
		}
	}

	function setUrl($url) {
		if ($url) {
			$this->url = $url;
		}
	}

	function setBody($body) {
		if ($body) {
			$this->body = $body;
		}
	}

	function setUrlTarget($url_target) {
		if ($url_target) {
			$this->url_target = $url_target;
		}
	}

	function setNewPicture(array $new_picture) {
		if ($new_picture['tmp_name'] && $new_picture['size'] && !$new_picture['error']) {
			$this->new_picture = $new_picture;
		}
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
	}

	function setMetaTitle($meta_title) {
		$this->meta_title = $this->_filter->sanitize($meta_title ?: $this->name, ['string', 'trim']);
	}

	function setMetaKeyword($meta_keyword) {
		$this->meta_keyword = $this->_filter->sanitize($meta_keyword ?: $this->name, ['string', 'trim']);
	}

	function setMetaDesc($meta_desc) {
		if ($meta_desc) {
			$this->meta_desc = substr(str_replace(['\r', '\n'], ['', ' '], $this->_filter->sanitize($meta_desc, ['string', 'trim'])), 0, 160);
		}
	}

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int') ?? 0;
	}

	function setPosition($position) {
		$this->position = $this->_filter->sanitize($position, 'int') ?? 0;
	}

	function beforeValidation() {
		$this->permalink = trim(preg_replace(['/[^\w\d\-\ ]/', '/ /', '/\-{2,}/'], ['', '-', '-'], strtolower($this->new_permalink ?: $this->name)), '-');
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		if ($this->url) {
			$validator->add('url', new Url([
				'message' => 'url tidak valid',
			]));
		}
		$validator->add('url_target', new InclusionIn([
			'message' => 'link target harus salah satu dari _self, _blank atau _parent',
			'domain'  => static::URL_TARGETS,
		]));
		$validator->add('permalink', new Uniqueness([
			'attribute' => 'permalink',
			'message'   => 'permalink sudah ada',
		]));
		if ($this->new_picture) {
			$max_size = $this->_upload_config->max_size;
			$validator->add('new_picture', new FileValidator([
				'maxSize'      => $max_size,
				'messageSize'  => 'ukuran file maksimal ' . $max_size,
				'allowedTypes' => ['image/jpeg', 'image/png'],
				'messageType'  => 'format gambar harus JPG atau PNG',
			]));
		}
		return $this->validate($validator);
	}

	function beforeSave() {
		if ($this->new_picture && !$this->picture) {
			$random = new Random;
			do {
				$this->picture = $random->hex(16) . '.jpg';
				if (!static::findFirstByPicture($this->picture)) {
					break;
				}
			} while (1);
		}
	}

	function beforeUpdate() {
		parent::beforeUpdate();
		if ($this->new_picture) {
			foreach ($this->thumbnails as $thumbnail) {
				unlink($this->_upload_config->path . $thumbnail);
			}
			$this->thumbnails = [];
		}
		$this->thumbnails = json_encode($this->thumbnails);
	}

	function afterSave() {
		if (!$this->new_picture) {
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
}