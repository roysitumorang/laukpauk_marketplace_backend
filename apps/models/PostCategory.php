<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class PostCategory extends ModelBase {
	public $id;
	public $name;
	public $permalink;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'post_categories';
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->hasMany('id', Post::class, 'post_category_id', [
			'alias'      => 'posts',
			'foreignKey' => [
				'message' => 'kategori tidak dapat dihapus karena memiliki konten',
			],
		]);
	}

	function setName(string $name) {
		$this->name = implode(' ', preg_split('/\s/', $name, -1, PREG_SPLIT_NO_EMPTY));
	}

	function beforeValidation() {
		$this->permalink = implode('-', preg_split('/\s/', preg_replace('/[^a-z\d\s]+/', '', strtolower($this->name)), -1, PREG_SPLIT_NO_EMPTY));
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'permalink'], new PresenceOf([
			'message' => [
				'name'      => 'nama harus diisi',
				'permalink' => 'permalink harus diisi',
			],
		]));
		$validator->add('name', new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}