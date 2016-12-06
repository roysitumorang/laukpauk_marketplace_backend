<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Url;

class PostComment extends BaseModel {
	public $id;
	public $post_id;
	public $name;
	public $email;
	public $website;
	public $body;
	public $ip_address;
	public $approved;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function getSource() {
		return 'post_comments';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->belongsTo('post_id', 'Application\Models\Post', 'id', [
			'alias'    => 'post',
			'reusable' => true,
		]);
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setEmail(string $email) {
		$this->email = $this->_filter->sanitize($email, ['string', 'trim']);
	}

	function setWebsite(string $website) {
		if ($website) {
			$this->website = $this->_filter->sanitize($website, ['string', 'trim']);
		}
	}

	function setBody($body) {
		if ($body) {
			$this->body = $this->_filter->sanitize($body, ['string', 'trim']);
		}
	}

	function setApproved($approved) {
		$this->approved = $this->_filter->sanitize($approved, 'int') ?? 0;
	}

	function beforeValidationOnCreate() {
		$this->approved   = 0;
		$this->ip_address = $this->getDI()->getRequest()->getClientAddress();
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'email', 'body'], new PresenceOf([
			'message' => [
				'name'  => 'nama harus diisi',
				'email' => 'email harus diisi',
				'body'  => 'comment harus diisi'
			]
		]));
		$validator->add('email', new Email([
			'message' => 'email tidak valid',
		]));
		if ($this->website) {
			$validator->add('website', new Url([
				'message' => 'website tidak valid',
			]));
		}
		return $this->validate($validator);
	}
}