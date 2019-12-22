<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\{Digit, InclusionIn, PresenceOf, StringLength, Uniqueness};

class BankAccount extends ModelBase {
	const BANKS = ['BCA', 'BNI', 'BRI', 'Mandiri', 'Bank Sumut'];

	public $id;
	public $bank;
	public $number;
	public $holder;
	public $published;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function initialize() {
		$this->setSource('bank_accounts');
		parent::initialize();
		$this->hasMany('id', Payment::class, 'bank_account_id', ['alias' => 'payments']);
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
		$validator->add('bank', new InclusionIn([
			'domain'  => static::BANKS,
			'message' => 'bank tidak valid',
		]));
		$validator->add('published', new InclusionIn([
			'domain'  => [0, 1],
			'message' => 'tampilkan tidak valid',
		]));
		return $this->validate($validator);
	}
}