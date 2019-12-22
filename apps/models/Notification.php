<?php

namespace Application\Models;

use Imagick;
use Phalcon\Http\Request\File;
use Phalcon\Validation;
use Phalcon\Validation\Validator\{
	Callback,
	StringLength,
};

class Notification extends ModelBase {
	public $id;
	public $user_id;
	public $title;
	public $message;
	public $image;
	public $new_image;
	public $admin_target_url;
	public $merchant_target_url;
	public $old_mobile_target_url;
	public $new_mobile_target_url;
	public $new_mobile_target_parameters;
	public $created_by;
	public $created_at;

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		$this->setSource('notifications');
		parent::initialize();
		$this->belongsTo('user_id', User::class, 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
		$this->hasManyToMany('id', NotificationRecipient::class, 'notification_id', 'user_id', User::class, 'id', ['alias' => 'recipients']);
	}

	function setTitle($title) {
		$this->title = $this->_filter->sanitize($title, ['string', 'trim']);
	}

	function setMessage($message) {
		$this->message = $this->_filter->sanitize($message, 'trim');
	}

	function setNewImage(File $new_image) {
		if ($new_image->getTempName() && $new_image->getSize() && !$new_image->getError()) {
			$this->new_image = $new_image;
			do {
				$this->image = bin2hex(random_bytes(16)) . '.jpg';
				if (!is_readable($this->getDI()->getConfig()->upload->path . $this->image) && !static::count(['image = ?0', 'bind' => [$this->image]])) {
					break;
				}
			} while (1);
		}
	}

	function setAdminTargetUrl($admin_target_url) {
		$this->admin_target_url = $this->_filter->sanitize($admin_target_url, ['string', 'trim']);
	}

	function setMerchantTargetUrl($merchant_target_url) {
		$this->merchant_target_url = $this->_filter->sanitize($merchant_target_url, ['string', 'trim']);
	}

	function setOldMobileTargetUrl($old_mobile_target_url) {
		$this->old_mobile_target_url = $this->_filter->sanitize($old_mobile_target_url, ['string', 'trim']);
	}

	function setNewMobileTargetUrl($new_mobile_target_url) {
		$this->new_mobile_target_url = $this->_filter->sanitize($new_mobile_target_url, ['string', 'trim']);
	}

	function setNewMobileTargetParameters($new_mobile_target_parameters) {
		$this->new_mobile_target_parameters = $new_mobile_target_parameters;
	}

	function beforeValidationOnCreate() {
		parent::beforeValidationOnCreate();
		if (!$this->new_mobile_target_url) {
			$this->new_mobile_target_url = 'tab.notification';
		}
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['title', 'admin_target_url', 'merchant_target_url', 'old_mobile_target_url', 'new_mobile_target_url'], new StringLength([
			'min' => [
				'title'                 => 1,
				'admin_target_url'      => 1,
				'merchant_target_url'   => 1,
				'old_mobile_target_url' => 1,
				'new_mobile_target_url' => 1,
			],
			'max' => [
				'title'                 => 200,
				'admin_target_url'      => 200,
				'merchant_target_url'   => 200,
				'old_mobile_target_url' => 200,
				'new_mobile_target_url' => 200,
			],
			'messageMinimum' => [
				'title'                 => 'judul harus diisi',
				'admin_target_url'      => 'link target admin harus diisi',
				'merchant_target_url'   => 'link target merchant harus diisi',
				'old_mobile_target_url' => 'link target mobile lama harus diisi',
				'new_mobile_target_url' => 'link target mobile baru harus diisi',
			],
			'messageMaximum' => [
				'title'                 => 'judul maksimal 200 karakter',
				'admin_target_url'      => 'link target admin maksimal 200 karakter',
				'merchant_target_url'   => 'link target merchant maksimal 200 karakter',
				'old_mobile_target_url' => 'link target mobile lama maksimal 200 karakter',
				'new_mobile_target_url' => 'link target mobile baru maksimal 200 karakter',
			]
		]));
		$validator->add('message', new Callback([
			'message'  => 'pesan maksimal 1024 karakter',
			'callback' => function($data) {
				return strlen($data->message) <= 1024;
			},
		]));
		if (!$this->id && $this->new_image) {
			$max_size = $this->getDI()->getConfig()->upload->max_size;
			$validator->add('new_image', new Callback([
				'message'  => 'gambar harus dalam format JPG/PNG dan ukuran gambar maksimal ' . $max_size,
				'callback' => function($data) use($max_size) {
					return $data->new_image->getSize() <= intval($max_size) * pow(1024, 2) &&
						in_array($data->new_image->getRealType(), ['image/jpeg', 'image/png']);
				},
			]));
		}
		return $this->validate($validator);
	}

	function beforeCreate() {
		if ($this->new_image) {
			$image = $this->getDI()->getConfig()->upload->path . $this->image;
			$this->new_image->moveTo($image);
			$image = new Imagick($image);
			$image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
			$image->writeImage();
		}
	}

	function push(array $recipients) {
		if (!$this->validation() || !$this->create()) {
			return false;
		}
		if (!$this->new_mobile_target_parameters) {
			$this->update(['new_mobile_target_parameters' => sprintf('{"notificationId":%d}', $this->id)]);
		}
		$onesignal_device_tokens = [];
		$fcm_device_tokens       = [];
		foreach ($recipients as $recipient) {
			$device_token_exists = false;
			if ($recipient->device_token) {
				$fcm_device_tokens[] = $recipient->device_token;
				$device_token_exists = true;
			} else {
				foreach ($recipient->devices as $device) {
					$onesignal_device_tokens[] = $device->token;
					$device_token_exists       = true;
				}
			}
			if ($device_token_exists || ($recipient->status == 1 && ($recipient->role_id == Role::SUPER_ADMIN || $recipient->role_id == Role::ADMIN))) {
				$relation = new NotificationRecipient([
					'notification_id' => $this->id,
					'user_id'         => $recipient->id,
				]);
				$relation->create();
			}
		}
		if ($onesignal_device_tokens) {
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
					'include_player_ids' => $onesignal_device_tokens,
					'priority'           => 10,
					'headings'           => ['en' => $this->title],
					'contents'           => ['en' => $this->message],
					'data'               => [
						'link'              => $this->old_mobile_target_url,
						'target_url'        => $this->new_mobile_target_url,
						'target_parameters' => json_decode($this->new_mobile_target_parameters),
					],
				]),
			]);
			curl_exec($ch);
			curl_close($ch);
		}
		if ($fcm_device_tokens) {
			$ch = curl_init('https://fcm.googleapis.com/fcm/send');
			curl_setopt_array($ch, [
				CURLOPT_POST           => 1,
				CURLOPT_HTTPHEADER     => [
					'Authorization: key=' . $this->getDI()->getConfig()->push_api_key,
					'Content-Type: application/json',
				],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_POSTFIELDS     => json_encode([
					'registration_ids' => $fcm_device_tokens,
					'priority'         => 'high',
					'notification'     => [
						'title'        => $this->title,
						'body'         => $this->message,
						'sound'        => 'default',
						'click_action' => 'FCM_PLUGIN_ACTIVITY',
						'icon'         => 'fcm_push_icon'
					],
					'data'             => [
						'id'                => $this->id,
						'title'             => $this->title,
						'message'           => $this->message,
						'image_url'         => $this->image ? $this->getDI()->getPictureRootUrl() . $this->image : null,
						'target_url'        => $this->new_mobile_target_url,
						'target_parameters' => json_decode($this->new_mobile_target_parameters),
					],
				]),
			]);
			curl_exec($ch);
			curl_close($ch);
		}
		return true;
	}
}