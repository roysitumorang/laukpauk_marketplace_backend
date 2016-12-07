<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class NotificationTemplate extends ModelBase {
	public $id;
	public $name;
	public $subject;
	public $url;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'notification_templates';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setSubject($subject) {
		$this->subject = $this->_filter->sanitize($subject, ['string', 'trim']);
	}

	function setUrl($url) {
		$this->url = $this->_filter->sanitize($url, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['name', 'subject', 'url'], new PresenceOf([
			'message' => [
				'name'    => 'nama harus diisi',
				'subject' => 'judul harus diisi',
				'url'     => 'url harus diisi',
			],
		]));
		$validator->add('name', new Uniqueness([
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}