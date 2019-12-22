<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Message extends ModelBase {
	public $id;
	public $subject;
	public $body;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		$this->setSource('messages');
		parent::initialize();
		$this->belongsTo('created_by', User::class, 'id', [
			'alias'      => 'sender',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'kategori harus diisi',
			],
		]);
		$this->hasManyToMany('id', MessageRecipient::class, 'message_id', 'user_id', User::class, 'id', ['alias' => 'users']);
	}

	function setSubject($subject) {
		$this->subject = $this->_filter->sanitize($subject, ['string', 'trim']);
	}

	function setBody($body) {
		$this->body = $this->_filter->sanitize($body, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['subject', 'body'], new PresenceOf([
			'message' => [
				'subject' => 'judul harus diisi',
				'body'    => 'pesan harus diisi',
			],
		]));
		return $this->validate($validator);
	}
}