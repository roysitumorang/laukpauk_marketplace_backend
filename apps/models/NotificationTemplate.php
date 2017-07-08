<?php

namespace Application\Models;

use Application\Models\ModelBase;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class NotificationTemplate extends ModelBase {
	public $id;
	public $name;
	public $title;
	public $admin_target_url;
	public $merchant_target_url;
	public $old_mobile_target_url;
	public $new_mobile_target_url;
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

	function setName($name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setTitle($title) {
		$this->title = $this->_filter->sanitize($title, ['string', 'trim']);
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

	function validation() {
		$validator = new Validation;
		$validator->add(['notification_type', 'name', 'title', 'target_url'], new PresenceOf([
			'message' => [
				'notification_type'     => 'tipe harus diisi',
				'name'                  => 'nama harus diisi',
				'title'                 => 'judul harus diisi',
				'admin_target_url'      => 'link target admin harus diisi',
				'merchant_target_url'   => 'link target merchant harus diisi',
				'old_mobile_target_url' => 'link target mobile lama harus diisi',
				'new_mobile_target_url' => 'link target mobile baru harus diisi',
			],
		]));
		$validator->add('name', new Uniqueness([
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}