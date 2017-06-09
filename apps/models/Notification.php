<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Notification extends ModelBase {
	public $id;
	public $notification_template_id;
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
		$this->belongsTo('notification_template_id', 'Application\Models\NotificationTemplate', 'id', [
			'alias'    => 'template',
			'reusable' => true,
		]);
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

	function push(array $tokens, array $message, array $payload = []) {
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL            => 'https://onesignal.com/api/v1/notifications',
			CURLOPT_POST           => 1,
			CURLOPT_HTTPHEADER     => [
				'Content-Type: application/json; charset=utf-8',
				'Authorization: Basic ' . $this->getDI()->getConfig()->onesignal->api_key,
			],
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_POSTFIELDS     => json_encode([
				'app_id'             => $this->getDI()->getConfig()->onesignal->app_id,
				'include_player_ids' => $tokens,
				'priority'           => 10,
				'headings'           => ['en' => $message['subject']],
				'contents'           => ['en' => $message['content']],
				'data'               => $payload,
			]),
		]);
		$response = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($response);
		if ($result->errors) {
			return false;
		}
		return $this->create();
	}
}