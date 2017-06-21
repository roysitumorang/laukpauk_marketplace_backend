<?php

namespace Application\Models;

use Phalcon\Mvc\Model;

class ModelBase extends Model {
	function initialize() {
		if (property_exists($this, 'updated_by') && property_exists($this, 'updated_at')) {
			$this->skipAttributesOnCreate([
				'updated_by',
				'updated_at',
			]);
		}
		if (property_exists($this, 'created_by') && property_exists($this, 'created_at')) {
			$this->skipAttributesOnUpdate([
				'created_by',
				'created_at',
			]);
		}
	}

	function beforeValidationOnCreate() {
		if (property_exists($this, 'created_by') && property_exists($this, 'created_at')) {
			$session          = $this->getDI()->getSession();
			$this->created_by = $this->created_by ?: ($session && $session->has('user_id') ? $session->get('user_id') : null);
			$this->created_at = $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s.u');
		}
	}

	function beforeValidationOnUpdate() {
		if (property_exists($this, 'updated_by') && property_exists($this, 'updated_at')) {
			$session          = $this->getDI()->getSession();
			$this->updated_by = $this->updated_by ?: ($session && $session->has('user_id') ? $session->get('user_id') : null);
			$this->updated_at = $this->getDI()->getCurrentDatetime()->format('Y-m-d H:i:s.u');
		}
	}
}