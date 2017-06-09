<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength;

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

	function send() {
		$ch           = curl_init();
		$config       = $this->getDI()->getConfig()->sms;
		$destinations = [];
		foreach ($this->recipients as $recipient) {
			$destinations[] = $recipient->mobile_phone;
		}
		curl_setopt_array($ch, [
			CURLOPT_URL            => sprintf('https://www.isms.com.my/isms_send.php?un=%s&pwd=%s&dstno=%s&msg=%s&type=1', urlencode($config->username), urlencode($config->password), implode(';', $destinations)),
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
		]);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result == 2000 ? $this->create() : false;
	}
}