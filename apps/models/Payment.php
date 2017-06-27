<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Payment extends ModelBase {
	const STATUS = [0 => 'Menunggu Konfirmasi', -1 => 'Ditolak', 1 => 'Diterima'];

	public $id;
	public $user_id;
	public $bank_account_id;
	public $payer_bank;
	public $payer_account_number;
	public $amount;
	public $status;
	public $code;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	function getSource() {
		return 'payments';
	}

	function initialize() {
		parent::initialize();
		$this->belongsTo('user_id', 'Application\Models\User', 'id', [
			'alias'    => 'user',
			'reusable' => true,
		]);
		$this->belongsTo('bank_account_id', 'Application\Models\BankAccount', 'id', [
			'alias'    => 'bankAccount',
			'reusable' => true,
		]);
	}

	function setPayerBank($payer_bank) {
		$this->payer_bank = $payer_bank;
	}

	function setPayerAccountNumber($payer_account_number) {
		$this->payer_account_number = $payer_account_number;
	}

	function setAmount($amount) {
		$this->amount = $amount;
	}

	function setStatus($status) {
		$this->status = $status;
	}

	function beforeValidationOnCreate() {
		parent::beforeValidationOnCreate();
		do {
			$this->code = random_int(111111, 999999);
			if (!static::findFirstByCode($this->code)) {
				break;
			}
		} while (1);
	}

	function validation() {
		$validator = new Validation;
		$validator->add(['payer_bank', 'payer_account_number', 'amount', 'status'], new PresenceOf([
			'message' => [
				'payer_bank'           => 'bank asal pembayaran harus diisi',
				'payer_account_number' => 'nomor rekening asal pembayaran harus diisi',
				'amount'               => 'jumlah pembayaran harus diisi',
				'status'               => 'status pembayaran harus diisi',
			],
		]));
		$validator->add(['payer_account_number', 'amount'], new Digit([
			'message' => [
				'payer_account_number' => 'nomor rekening asal pembayaran harus dalam bentuk angka',
				'amount'               => 'jumlah pembayaran harus dalam bentuk angka',
			],
		]));
		$validator->add('status', new InclusionIn([
			'message' => 'status pembayaran antara -1, 0 atau 1',
			'domain'  => array_keys(static::STATUS),
		]));
		$validator->add('code', new Uniqueness([
			'message' => 'kode pembayaran sudah ada',
		]));
		return $this->validate($validator);
	}

	function approve() {
		return $this->user->update(['deposit' => $this->user->deposit + $this->amount]) && $this->update(['status' => 1]);
	}

	function reject() {
		return $this->update(['status' => -1]);
	}
}