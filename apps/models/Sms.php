<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\StringLength;
use SimpleXMLElement;
use stdClass;

class Sms extends ModelBase {
	public $id;
	public $user_id;
	public $body;
	public $created_by;
	public $created_at;

	private $_config;

	function onConstruct() {
		$this->_config = $this->getDI()->getConfig()->sms;
	}

	function initialize() {
		$this->setSource('sms');
		parent::initialize();
		$this->belongsTo('user_id', User::class, 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
		$this->hasManyToMany('id', SmsRecipient::class, 'sms_id', 'user_id', User::class, 'id', ['alias' => 'recipients']);
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

	function send(array $recipients) {
		if (!$recipients) {
			return false;
		}
		$this->create();
		$master = curl_multi_init();
		$active = null;
		$nodes  = [];
		foreach ($recipients as $recipient) {
			$curl = curl_init();
			$node = new stdClass;
			curl_setopt_array($curl, [
				CURLOPT_URL            => sprintf('%s?userkey=%s&passkey=%s&nohp=%s&pesan=%s', $this->_config->send_endpoint, urlencode($this->_config->username), urlencode($this->_config->password), $recipient->mobile_phone, urlencode($this->body)),
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => 0,
			]);
			curl_multi_add_handle($master, $curl);
			$node->id   = $recipient->id;
			$node->curl = $curl;
			$nodes[]    = $node;
		}
		do {
			$execution = curl_multi_exec($master, $active);
		} while ($execution == CURLM_CALL_MULTI_PERFORM);
		while ($active && $execution == CURLM_OK) {
			if (curl_multi_select($master) != -1) {
				do {
					$execution = curl_multi_exec($master, $active);
				} while ($master == CURLM_CALL_MULTI_PERFORM);
			}
		}
		foreach ($nodes as $node) {
			$result        = new SimpleXMLElement(curl_multi_getcontent($node->curl));
			$message       = $result->message;
			$sms_recipient = new SmsRecipient;
			$sms_recipient->create([
				'sms_id'       => $this->id,
				'user_id'      => $node->id,
				'mobile_phone' => $message->to,
				'status'       => $message->status == 0 ? 1 : 0,
			]);
			curl_multi_remove_handle($master, $node->curl);
		}
		curl_multi_close($master);
		$nodes = null;
		return $this;
	}
}