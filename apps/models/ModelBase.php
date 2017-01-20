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
				'include_player_ids' => [$token],
				'priority'           => 10,
				'headings'           => ['en' => $message['title']],
				'contents'           => ['en' => $message['content']],
				'data'               => [],
			]),
		]);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}