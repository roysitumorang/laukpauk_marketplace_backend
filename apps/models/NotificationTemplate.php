<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class NotificationTemplate extends ModelBase {
	public $id;
	public $notification_type;
	public $name;
	public $subject;
	public $link;
	public $target_url;
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

	function setNotificationType($notification_type) {
		$this->notification_type = $notification_type;
	}

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setSubject($subject) {
		$this->subject = $this->_filter->sanitize($subject, ['string', 'trim']);
	}

	function setLink($link) {
		$this->link = $this->_filter->sanitize($link, ['string', 'trim']);
	}

	function setTargetUrl($target_url) {
		$this->target_url = $this->_filter->sanitize($target_url, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['notification_type', 'name', 'subject', 'target_url'], new PresenceOf([
			'message' => [
				'notification_type' => 'tipe harus diisi',
				'name'              => 'nama harus diisi',
				'subject'           => 'judul harus diisi',
				'target_url'        => 'url harus diisi',
			],
		]));
		$validator->add('notification_type', new InclusionIn([
			'domain'  => Notification::TYPES,
			'message' => 'tipe salah satu dari mobile atau web',
		]));
		$validator->add('name', new Uniqueness([
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}