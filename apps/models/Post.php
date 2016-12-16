<?php

namespace Application\Models;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Image;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Url;

class Post extends ModelBase {
	public $id;
	public $post_category_id;
	public $subject;
	public $permalink;
	public $new_permalink;
	public $custom_link;
	public $body;
	public $picture;
	public $new_picture;
	public $thumbnails;
	public $meta_title;
	public $meta_desc;
	public $meta_keyword;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;
	private $_upload_config;

	function getSource() {
		return 'posts';
	}

	function onConstruct() {
		$this->_filter        = $this->getDI()->getFilter();
		$this->_upload_config = $this->getDI()->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->hasMany('id', 'Application\Models\PostComment', 'post_id', [
			'alias'      => 'comments',
			'foreignKey' => [
				'message' => 'content tidak dapat dihapus karena memiliki comment',
			],
		]);
		$this->belongsTo('post_category_id', 'Application\Models\Post', 'id', [
			'alias'    => 'category',
			'reusable' => true,
		]);
	}

	function setSubject(string $subject) {
		$this->subject = $this->_filter->sanitize($subject, ['string', 'trim']);
	}

	function setNewPermalink($new_permalink) {
		if ($new_permalink) {
			$this->new_permalink = $this->_filter->sanitize($new_permalink, ['string', 'trim']);
		}
	}

	function setCustomLink($custom_link) {
		if ($custom_link) {
			$this->custom_link = $custom_link;
		}
	}

	function setBody($body) {
		if ($body) {
			$this->body = $body;
		}
	}

	function setNewPicture(array $new_picture) {
		$this->new_picture = $new_picture;
	}

	function setThumbnails(array $thumbnails = null) {
		$this->thumbnails = array_filter($thumbnails ?? []);
	}

	function setMetaTitle($meta_title) {
		$this->meta_title = $this->_filter->sanitize($meta_title ?: $this->subject, ['string', 'trim']);
	}

	function setMetaKeyword($meta_keyword) {
		$this->meta_keyword = $this->_filter->sanitize($meta_keyword ?: $this->subject, ['string', 'trim']);
	}

	function setMetaDesc($meta_desc) {
		if ($meta_desc) {
			$this->meta_desc = substr(str_replace(['\r', '\n'], ['', ' '], $this->_filter->sanitize($meta_desc, ['string', 'trim'])), 0, 160);
		}
	}

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int') ?? 0;
	}

	function beforeValidation() {
		$this->permalink = trim(preg_replace(['/[^\w\d\-\ ]/', '/ /', '/\-{2,}/'], ['', '-', '-'], strtolower($this->new_permalink ?: $this->subject)), '-');
	}

	function validation() {
		$validator = new Validation;
		$validator->add('subject', new PresenceOf([
			'message' => 'judul harus diisi',
		]));
		if ($this->custom_link) {
			$validator->add('custom_link', new Url([
				'message' => 'link tambahan tidak valid',
			]));
		}
		$validator->add('permalink', new Uniqueness([
			'attribute' => 'permalink',
			'message'   => 'permalink sudah ada',
		]));
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
	}

	function afterFetch() {
		$this->thumbnails = json_decode(trim(stripslashes($this->thumbnails), '"'));
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
				$this->skipAttributes(['updated_by', 'updated_at']);
				$this->update(['thumbnails' => $this->thumbnails]);
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