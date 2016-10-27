<?php

namespace Application\Models;

use Phalcon\Http\Request\File;
use Phalcon\Image\Adapter\Gd;
use Phalcon\Mvc\Model\Message;

class ProductCategory extends BaseModel {
	public $id;
	public $parent_id;
	public $name;
	public $permalink;
	public $picture;
	public $published;
	public $description;
	public $meta_title;
	public $meta_desc;
	public $meta_keyword;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_upload_config;
	private $_tmp_file;

	function getSource() {
		return 'product_categories';
	}

	function onConstruct() {
		$this->_upload_config = $this->getDI()->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->skipAttributesOnUpdate(['parent_id']);
		$this->keepSnapshots(true);
		$this->belongsTo('parent_id', 'Application\Models\ProductCategory', 'id', ['alias' => 'parent']);
		$this->hasMany('id', 'Application\Models\ProductCategory', 'parent_id', ['alias' => 'sub_categories']);
		$this->hasMany('id', 'Application\Models\Thumbnail', 'reference_id', [
			'alias'  => 'thumbnails',
			'params' => [
				'conditions' => "[Application\Models\Thumbnail].reference_type = 'product_category'",
			],
		]);
		$this->hasMany('id', 'Application\Models\Product', 'product_category_id', ['alias'  => 'products']);
	}

	function setParentId(int $parent_id = null) {
		if (!$parent_id) {
			return $this;
		}
		$params = ['id = ?1', 'bind' => [1 => $parent_id]];
		if ($this->id) {
			$params[0]         .= ' AND id != ?2';
			$params['bind'][2]  = $this->id;
		}
		if (!static::findFirst($params)) {
			$this->appendMessage(new Message('kategori parent tidak valid', 'parent_id', 'InvalidValue'));
		}
		return $this;
	}

	function setName($name) {
		$this->name = $name;
		if (!$name) {
			$this->appendMessage(new Message('nama harus diisi', 'name', 'InvalidValue'));
			return $this;
		}
		$params = ['name = ?1', 'bind' => [1 => $name]];
		if ($this->id) {
			$params[0]         .= ' AND id != ?2';
			$params['bind'][2]  = $this->id;
		}
		if (static::findFirst($params)) {
			$this->appendMessage(new Message('nama sudah ada', 'name', 'InvalidValue'));
		}
		return $this;
	}

	function setPermalink($permalink) {
		$this->permalink = $permalink ?: preg_replace('/[^a-z0-9-]+/', '-', strtolower($this->name));
		$params          = ['permalink = ?1', 'bind' => [1 => $permalink]];
		if ($this->id) {
			$params[0]         .= ' AND id != ?2';
			$params['bind'][2]  = $this->id;
		}
		if (static::findFirst($params)) {
			$this->appendMessage(new Message('permalink sudah ada', 'permalink', 'InvalidValue'));
		}
		return $this;
	}

	function setPicture(File $picture = null) {
		if (!$picture || !$picture->getTempName() || $picture->getError()) {
			return $this;
		}
		$max_size  = $this->_upload_config->max_size;
		$mime_type = exif_imagetype($picture->getTempName());
		if ($picture->getSize() > $max_size) {
			$this->appendMessage(new Message('ukuran gambar maksimum 2 MB', 'picture', 'InvalidValue'));
			return $this;
		}
		if ($mime_type != IMAGETYPE_JPEG && $mime_type != IMAGETYPE_PNG) {
			$this->appendMessage(new Message('format gambar harus JPG atau PNG', 'picture', 'InvalidValue'));
			return $this;
		}
		$this->_tmp_file = $picture;
		$image           = new Gd($picture->getTempName());
		$this->width     = $image->getWidth();
		$this->height    = $image->getHeight();
		if (!$this->picture) {
			do {
				$this->picture = bin2hex(random_bytes(16)) . '.jpg';
				if (!is_readable($this->_upload_config->path . $this->picture) && !static::findFirst("picture = '{$this->picture}'")) {
					break;
				}
			} while (1);
		}
		return $this;
	}

	function setPublished(int $published) {
		$this->published = $published ?? 1;
		return $this;
	}

	function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	function setMetaTitle($meta_title) {
		$this->meta_title = $meta_title ?: $this->name;
		return $this;
	}

	function setMetaDesc($meta_desc) {
		$this->meta_desc = substr(strip_tags(str_replace(['\r', '\n'], ['', ' '], $meta_desc)), 0, 160);
		return $this;
	}

	function setMetaKeyword($meta_keyword) {
		$this->meta_keyword = $meta_keyword ?: $this->name;
		return $this;
	}

	function afterSave() {
		if (!$this->_tmp_file) {
			return true;
		}
		foreach ($this->thumbnails as $thumbnail) {
			$thumbnail->delete();
		}
		$file = $this->_upload_config->path . $this->picture;
		$this->_tmp_file->moveTo($file);
		$image = new Gd($file);
		$image->save($file, 100);
	}

	function beforeDelete() {
		$file = $this->_upload_config->path . $this->picture;
		if (is_readable($file)) {
			unlink($file);
		}
		foreach ($this->thumbnails as $thumbnail) {
			$thumbnail->delete();
		}
	}
}