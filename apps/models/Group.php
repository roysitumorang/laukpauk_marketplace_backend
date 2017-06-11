<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Group extends ModelBase {
	public $id;
	public $name;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'groups';
	}

	function initialize() {
		parent::initialize();
		$this->hasManyToMany('id', 'Application\Models\ProductGroup', 'group_id', 'product_id', 'Application\Models\Product', 'id', ['alias' => 'products']);
	}

	function setName(string $name) {
		$this->name = $name;
	}

	function setPublished(string $published) {
		$this->published = $published;
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
		$validator->add('published', new Between([
			'minimum' => 0,
			'maximum' => 1,
			'message' => 'tampilkan harus antara 0 and 1',
		]));
		return $this->validate($validator);
	}
}