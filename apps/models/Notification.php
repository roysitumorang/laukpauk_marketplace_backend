<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Notification extends ModelBase {
	public $id;
	public $subject;
	public $link;
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
		$this->hasManyToMany('id', 'Application\Models\NotificationRecipient', 'notification_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'recipients']);
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