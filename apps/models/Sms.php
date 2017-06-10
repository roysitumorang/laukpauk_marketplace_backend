<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength;
use SimpleXMLElement;

class Sms extends ModelBase {
	public $id;
	public $body;
	public $created_by;
	public $created_at;

	function getSource() {
		return 'sms';
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
		return $this->validate($validator);
	}

	function send(array $destinations) {
		$ch     = curl_init();
		$config = $this->getDI()->getConfig()->sms;
		curl_setopt_array($ch, [
			CURLOPT_URL            => sprintf('https://reguler.zenziva.net/apps/smsapi.php?userkey=%s&passkey=%s&nohp=%s&pesan=%s', urlencode($config->username), urlencode($config->password), implode(';', $destinations), urlencode($this->body)),
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
		]);
		$response = curl_exec($ch);
		curl_close($ch);
		$result = new SimpleXMLElement($response);
		return $result && $result->message && $result->message->status == 0 ? $this->create() : false;
	}
}