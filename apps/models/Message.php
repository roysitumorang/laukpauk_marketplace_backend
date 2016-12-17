<?php

namespace Application\Models;

use Application\Models\ModelBase;
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

	function getSource() {
		return 'messages';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('created_by', 'Application\Models\User', 'id', [
			'alias'      => 'sender',
			'reusable'   => true,
			'foreignKey' => [
				'allowNulls' => false,
				'message'    => 'kategori harus diisi',
			],
		]);
		$this->hasManyToMany('id', 'Application\Models\MessageRecipient', 'message_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'users']);
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