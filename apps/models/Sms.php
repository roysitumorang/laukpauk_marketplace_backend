<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\StringLength;
use SimpleXMLElement;

class Sms extends ModelBase {
	public $id;
	public $body;
	public $recipients;
	public $created_by;
	public $created_at;

	private $_config;

	function getSource() {
		return 'sms';
	}

	function onConstruct() {
		$this->_config = $this->getDI()->getConfig()->sms;
	}

	function initialize() {
		parent::initialize();
		$this->hasManyToMany('id', 'Application\Models\SmsRecipient', 'sms_id', 'user_id', 'Application\Models\User', 'id', ['alias' => 'recipients']);
	}

	function setBody(string $body) {
		$this->body = $body;
	}

	function validation() {
		$validator = new Validation;
		$validator->add('body', new StringLength([
			'min'            => 1,
			'max'            => 140,
			'messageMinimum' => 'pesan harus diisi',
			'messageMaximum' => 'pesan maksimal 140 karakter',
		]));
		$validator->add('recipients', new Callback([
			'callback' => function($data) {
				$ch = curl_init();
				curl_setopt_array($ch, [
					CURLOPT_URL            => sprintf('%s?userkey=%s&passkey=%s', $this->_config->balance_endpoint, urlencode($this->_config->username), urlencode($this->_config->password)),
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_SSL_VERIFYPEER => 0,
				]);
				$response = curl_exec($ch);
				curl_close($ch);
				$result = new SimpleXMLElement($response);
				return $result->message->value >= count($data->recipients);
			},
			'message' => 'kredit SMS kurang',
		]));
		return $this->validate($validator);
	}

	function send() {
		if (!$this->recipients) {
			return false;
		}
		$ch = curl_init();
		foreach ($this->recipients as $recipient) {
			curl_setopt_array($ch, [
				CURLOPT_URL            => sprintf('%s?userkey=%s&passkey=%s&nohp=%s&pesan=%s', $this->_config->send_endpoint, urlencode($this->_config->username), urlencode($this->_config->password), $recipient, urlencode($this->body)),
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => 0,
			]);
			$response = curl_exec($ch);
			$result   = new SimpleXMLElement($response);
			if (!$response || !$result || !$result->message || $result->message->status != 0) {
				curl_close($ch);
				return false;
			}
		}
		curl_close($ch);
		return $this->create();
	}
}