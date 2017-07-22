<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class ProductGroup extends ModelBase {
	public $id;
	public $user_id;
	public $name;
	public $keywords;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'product_groups';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'      => 'user',
			'reusable'   => true,
		]);
		$this->hasManyToMany('id', 'Application\Models\ProductGroupMember', 'product_group_id', 'product_id', 'Application\Models\Product', 'id', ['alias' => 'products']);
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
			'message' => 'nama sudah ada',
		]));
		$validator->add('published', new Between([
			'minimum' => 0,
			'maximum' => 1,
			'message' => 'tampilkan harus antara 0 and 1',
		]));
		return $this->validate($validator);
	}

	function beforeSave() {
		$this->keywords = $this->getDI()->getDb()->fetchColumn("SELECT TO_TSVECTOR('simple', '{$this->name}')");
	}
}