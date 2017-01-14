<?php

namespace Application\Models;

use Phalcon\Mvc\Model;

class ModelBase extends Model {
	function initialize() {
		$this->skipAttributesOnCreate([
			'updated_by',
			'updated_at',
		]);
		$this->skipAttributesOnUpdate([
			'created_by',
			'created_at',
		]);
	}

	function beforeValidationOnCreate() {
		$this->created_by = $this->created_by ?? $this->getDI()->getSession()->get('user_id');
		$this->created_at = $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s.u');
	}

	function beforeValidationOnUpdate() {
		$this->updated_by = $this->updated_by ?? $this->getDI()->getSession()->get('user_id');
		$this->updated_at = $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s.u');
	}

	protected function _sendPushNotification($token, array $message) {
		$ch               = curl_init();
		$message['sound'] = 'default';
		curl_setopt_array($ch, [
			CURLOPT_URL            => 'https://fcm.googleapis.com/fcm/send',
			CURLOPT_POST           => 1,
			CURLOPT_HTTPHEADER     => [
				'Authorization: key=' . $this->getDI()->getConfig()->firebase_api_key,
				'Content-Type: application/json',
			],
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_POSTFIELDS     => json_encode([
				'to'           => $token,
				'priority'     => 'high',
				'notification' => $message,
			]),
		]);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}