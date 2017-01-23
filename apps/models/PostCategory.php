<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class PostCategory extends ModelBase {
	public $id;
	public $name;
	public $permalink;
	public $new_permalink;
	public $allow_comments;
	public $comment_moderation;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function getSource() {
		return 'post_categories';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->hasMany('id', 'Application\Models\Post', 'post_category_id', [
			'alias'      => 'posts',
			'foreignKey' => [
				'message' => 'kategori tidak dapat dihapus karena memiliki content',
			],
		]);
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setNewPermalink($new_permalink) {
		if ($new_permalink) {
			$this->new_permalink = $this->_filter->sanitize($new_permalink, ['string', 'trim']);
		}
	}

	function setAllowComments($allow_comments) {
		$this->allow_comments = $this->_filter->sanitize($allow_comments, 'int') ?? 0;
	}

	function setCommentModeration($comment_moderation) {
		$this->comment_moderation = $this->_filter->sanitize($comment_moderation, 'int') ?? 0;
	}

	function setPublished($published) {
		$this->published = $this->_filter->sanitize($published, 'int') ?? 0;
	}

	function beforeValidation() {
		$this->permalink = trim(preg_replace(['/[^\w\d\-\ ]/', '/ /', '/\-{2,}/'], ['', '-', '-'], strtolower($this->new_permalink ?: $this->name)), '-');
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		$validator->add('name', new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		if ($this->new_permalink) {
			$validator->add('permalink', new Uniqueness([
				'attribute' => 'permalink',
				'message'   => 'permalink sudah ada',
			]));
		}
		return $this->validate($validator);
	}
}