<?php

namespace Application\Models;

use Phalcon\Utils\Slug;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Post extends ModelBase {
	public $id;
	public $post_category_id;
	public $permalink;
	public $subject;
	public $body;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function initialize() {
		$this->setSource('posts');
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('post_category_id', PostCategory::class, 'id', [
			'alias'    => 'category',
			'reusable' => true,
		]);
	}

	function setSubject($subject) {
		$this->subject = $subject;
	}

	function setBody($body) {
		$this->body = $body;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['subject', 'body'], new PresenceOf([
			'message' => [
				'subject' => 'judul harus diisi',
				'body'    => 'konten harus diisi'
			],
		]));
		$validator->add('subject', new Uniqueness([
			'message' => 'judul sudah ada',
		]));
		return $this->validate($validator);
	}

	function beforeSave() {
		if (!$this->permalink) {
			$this->permalink = Slug::generate($this->subject);
		}
	}
}