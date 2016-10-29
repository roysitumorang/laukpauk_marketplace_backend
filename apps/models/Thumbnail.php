<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Image\Adapter\Gd;

class Thumbnail extends BaseModel {
	public $id;
	public $reference_type;
	public $reference_id;
	public $width;
	public $height;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	public $original_file;
	private $_upload_config;

	function getSource() {
		return 'thumbnails';
	}

	function onConstruct() {
		$this->_upload_config = $this->getDI()->getConfig()->upload;
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('parent_id', 'Application\Models\Thumbnail', 'id', ['alias' => 'parent']);
		$this->hasMany('id', 'Application\Models\Thumbnail', 'parent_id', ['alias' => 'children']);
	}

	static function generate(string $reference_type, int $reference_id, string $original_file, int $width, int $height) {
		$instance = static::findFirst(['reference_type = :reference_type: AND reference_id = :reference_id: AND width = :width: AND height = :height:', 'bind' => [
			'reference_type' => $reference_type,
			'reference_id'   => $reference_id,
			'width'          => $width,
			'height'         => $height,
		]]);
		if (!$instance) {
			$instance = new static;
			$instance->reference_type = $reference_type;
			$instance->reference_id   = $reference_id;
			$instance->original_file  = $original_file;
			$instance->width          = $width;
			$instance->height         = $height;
			$instance->create();
		}
		return $instance;
	}

	function afterSave() {
		$file  = $this->_upload_config->path . $this->name();
		$image = new Gd($this->_upload_config->path . $this->original_file);
		$image->resize($this->width, $this->height);
		$image->save($file, 100);
	}

	function beforeDelete() {
		$file = $this->_upload_config->path . $this->name();
		if (is_readable($file)) {
			unlink($file);
		}
	}

	function name() {
		return $this->id . '.jpg';
	}

	protected function _validation() {
		$validator = new Validation;
		$validator->add(['reference_type', 'reference_id', 'width', 'height'], new Uniqueness([
			'model'   => $this,
			'message' => 'sudah ada',
		]));
		return $validator->validate($this);
	}
}