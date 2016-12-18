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
}