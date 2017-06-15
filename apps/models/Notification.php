<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\StringLength;

class Notification extends ModelBase {
	const TYPES = ['mobile', 'web'];

	public $id;
	public $type;
	public $user_id;
	public $title;
	public $message;
	public $target_url;
	public $target_parameters;
	public $created_by;
	public $created_at;

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
		$this->hasManyToMany('id', 'Application\Models\NotificationRecipient', 'notification_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'recipients']);
	}

	function setType($type) {
		$this->type = $type;
	}

	function setTitle($title) {
		$this->title = $this->_filter->sanitize($title, ['string', 'trim']);
	}

	function setMessage($message) {
		$this->message = $this->_filter->sanitize($message, ['string', 'trim']);
	}

	function setTargetUrl($target_url) {
		$this->target_url = $this->_filter->sanitize($target_url, ['string', 'trim']);
	}

	function validation() {
		$validator = new Validation;
		$validator->add('type', new InclusionIn([
			'domain'  => static::TYPES,
			'message' => 'tipe salah satu dari mobile atau web',
		]));
		$validator->add(['title', 'message', 'target_url'], new StringLength([
			'min' => [
				'title'      => 1,
				'message'    => 1,
				'target_url' => 1,
			],
			'max' => [
				'title'      => 200,
				'message'    => 1024,
				'target_url' => 200,
			],
			'messageMinimum' => [
				'title'      => 'judul harus diisi',
				'message'    => 'pesan harus diisi',
				'target_url' => 'link target harus diisi',
			],
			'messageMaximum' => [
				'title'      => 'judul maksimal 200 karakter',
				'message'    => 'pesan maksimal 1024 karakter',
				'target_url' => 'link target maksimal 200 karakter',
			]
		]));
		return $this->validate($validator);
	}

	function push(array $tokens, array $content, array $payload = []) {
		if (!isset($payload['target_url'])) {
			$payload['target_url'] = $this->target_url = 'tab.notification';
		}
		if (is_array($payload['target_parameters']) && !empty($payload['target_parameters'])) {
			$this->target_parameters = json_encode($payload['target_parameters']);
		}
		if (!$this->create()) {
			return false;
		}
		if (!isset($payload['target_parameters'])) {
			$payload['target_parameters'] = ['notificationId' => $this->id];
			$this->update(['target_parameters' => json_encode($payload['target_parameters'])]);
		}
		$config = $this->getDI()->getConfig()->onesignal;
		$ch     = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL            => 'https://onesignal.com/api/v1/notifications',
			CURLOPT_POST           => 1,
			CURLOPT_HTTPHEADER     => [
				'Content-Type: application/json; charset=utf-8',
				'Authorization: Basic ' . $config->api_key,
			],
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_POSTFIELDS     => json_encode([
				'app_id'             => $config->app_id,
				'include_player_ids' => $tokens,
				'priority'           => 10,
				'headings'           => ['en' => $content['title'] ?: ''],
				'contents'           => ['en' => $content['message']],
				'data'               => $payload,
			]),
		]);
		curl_exec($ch);
		curl_close($ch);
		return true;
	}
}