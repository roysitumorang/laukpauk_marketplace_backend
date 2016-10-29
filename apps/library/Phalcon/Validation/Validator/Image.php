<?php

namespace Phalcon\Validation\Validator;

use InvalidArgumentException;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class Image extends Validator {
	function validate(Validation $validation, $attribute) : bool {
		$value = $validation->getValue($attribute);
		if (!is_array($value)) {
			throw new InvalidArgumentException('Attribute value must be an array');
		}
		if (!$value['tmp_name'] || $value['error']) {
			return true;
		}
		$code = $this->getOption('code');
		if (is_array($code)) {
			$code = $code[$attribute];
		}
		$code || $code = 'InvalidValue';
		if ($value['error'] === UPLOAD_ERR_INI_SIZE) {
			$validation->appendMessage(new Message($validation->getDefaultMessage('FileIniSize'), $attribute, $code));
			return false;
		}
		if (!isset($value['error']) || !isset($value['tmp_name']) || $value['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($value['tmp_name'])) {
			$validation->appendMessage(new Message($validation->getDefaultMessage('FileEmpty'), $attribute, $code));
			return false;
		}
		if ($this->getOption('max_size')) {
			$max_size   = $this->getOption('max_size');
			$byte_units = [
				'B' =>   0,
				'K' =>  10,
				'KB' => 10,
				'M' =>  20,
				'MB' => 20,
				'G' =>  30,
				'GB' => 30,
				'T' =>  40,
				'TB' => 40,
			];
			$unit       = 'B';
			$matches    = null;
			preg_match("/^([0-9]+(?:\\.[0-9]+)?)(" . implode('|', array_keys($byte_units)) . ")?$/Di", $max_size, $matches);
			if (isset($matches[2])) {
				$unit = $matches[2];
			}
			$bytes = floatval($matches[1]) * pow(2, $byte_units[$unit]);
			if (floatval($value['size']) > floatval($bytes)) {
				$validation->appendMessage(new Message($this->getOption('message_size'), $attribute, $code));
				return false;
			}
		}
		$mime_type = exif_imagetype($value['tmp_name']);
		if ($mime_type != IMAGETYPE_JPEG && $mime_type != IMAGETYPE_PNG) {
			$validation->appendMessage(new Message($this->getOption('message_type'), $attribute, $code));
			return false;
		}
		return true;
	}

	function isAllowEmpty(Validation $validation, $attribute) : bool {
		$value = $validation->getValue($attribute);
		return empty($value) || $value['error'] === UPLOAD_ERR_NO_FILE;
	}
}