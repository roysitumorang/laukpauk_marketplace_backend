<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Notification extends ModelBase {
	public $id;
	public $user_id;
	public $subject;
	public $link;
	public $read_at;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'notifications';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
	}

	function setSubject($subject) {
		$this->subject = $this->_filter->sanitize($subject, ['string', 'trim']);
	}

	function setLink($link) {
		$this->link = $this->_filter->sanitize($link, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['subject', 'link'], new PresenceOf([
			'message' => [
				'subject' => 'nama harus diisi',
				'link'    => 'link harus diisi',
			],
		]));
		return $this->validate($validator);
	}
}