<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Post extends ModelBase {
	public $id;
	public $post_category_id;
	public $body;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'posts';
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('post_category_id', 'Application\Models\PostCategory', 'id', [
			'alias'    => 'category',
			'reusable' => true,
		]);
	}

	function setBody($body) {
		$this->body = $body;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['post_category_id', 'body'], new PresenceOf([
			'message' => [
				'post_category_id' => 'kategori harus diisi',
				'body'             => 'konten harus diisi',
			],
		]));
		return $this->validate($validator);
	}
}