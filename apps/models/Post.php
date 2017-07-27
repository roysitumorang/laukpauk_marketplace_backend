<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Post extends ModelBase {
	public $id;
	public $user_id;
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
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
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
		$validator->add(['user_id', 'post_category_id'], new Uniqueness([
			'message' => 'konten sudah ada',
		]));
		return $this->validate($validator);
	}
}