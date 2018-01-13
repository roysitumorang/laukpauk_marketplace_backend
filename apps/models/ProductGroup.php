<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\{Between, PresenceOf, Uniqueness, Url};

class ProductGroup extends ModelBase {
	public $id;
	public $name;
	public $keywords;
	public $url;
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
		$this->hasManyToMany('id', ProductGroupMember::class, 'product_group_id', 'product_id', Product::class, 'id', ['alias' => 'products']);
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
		if ($this->url) {
			$validator->add('url', new Url([
				'message' => 'link tidak valid',
			]));
			$validator->add('url', new Uniqueness([
				'message' => 'link sudah ada',
			]));
		}
		$validator->add('published', new Between([
			'minimum' => 0,
			'maximum' => 1,
			'message' => 'tampilkan harus antara 0 and 1',
		]));
		return $this->validate($validator);
	}

	function beforeSave() {
		$this->name     = implode(' ', preg_split('/\s/', $this->name, -1, PREG_SPLIT_NO_EMPTY));
		$this->keywords = $this->getDI()->getDb()->fetchColumn("SELECT TO_TSVECTOR('simple', '{$this->name}')");
		$this->url      = $this->url ?: null;
	}
}