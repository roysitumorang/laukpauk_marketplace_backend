<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;

class BankAccount extends ModelBase {
	public $id;
	public $bank;
	public $number;
	public $holder;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'bank_accounts';
	}

	function setBank($bank) {
		$this->bank = $bank;
	}

	function setNumber($number) {
		$this->number = $number;
	}

	function setHolder($holder) {
		$this->holder = $holder;
	}

	function setPublished($published) {
		$this->published = $published;
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['bank', 'number', 'holder'], new PresenceOf([
			'message' => [
				'bank'   => 'bank harus diisi',
				'number' => 'nomor rekening harus diisi',
				'holder' => 'nama pemegang rekening harus diisi',
			],
		]));
		$validator->add('number', new StringLength([
			'min'            => 8,
			'max'            => 15,
			'messageMinimum' => 'nomor rekening minimal 8 angka',
			'messageMaximum' => 'nomor rekening maksimal 15 angka',
		]));
		$validator->add('number', new Digit([
			'message' => 'nomor rekening harus dalam bentuk angka',
		]));
		$validator->add(['bank', 'number'], new Uniqueness([
			'message' => 'nomor rekening sudah ada',
		]));
		$validator->add('published', new InclusionIn([
			'domain'  => [0, 1],
			'message' => 'tampilkan tidak valid',
		]));
		return $this->validate($validator);
	}
}