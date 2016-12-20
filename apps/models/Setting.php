<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Setting extends ModelBase {
	public $id;
	public $name;
	public $value;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function initialize() {
		$this->getDI()->getFilter();
	}

	function getSource() {
		return 'settings';
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setValue(string $value) {
		$this->value = $this->_filter->sanitize($value, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'value', new PresenceOf([
			'message' => [
				'name'  => 'nama harus diisi',
				'value' => 'nilai harus diisi',
			]
		])]);
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
