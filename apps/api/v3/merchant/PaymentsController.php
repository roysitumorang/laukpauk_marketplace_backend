<?php

namespace Application\Api\V3\Merchant;

use Application\Models\BankAccount;
use Application\Models\Payment;
use Ds\Set;
use Exception;
use Phalcon\Db\Enum;

class PaymentsController extends ControllerBase {
	function indexAction() {
		$payments     = [];
		$limit        = 10;
		$page         = $this->dispatcher->getParam('page', 'int');
		$current_page = $page > 0 ? $page : 1;
		$offset       = ($current_page - 1) * $limit;
		$result       = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.code,
				a.amount,
				a.payer_bank,
				a.payer_account_number,
				a.status,
				b.bank,
				b.holder,
				b.number
			FROM
				payments a
				JOIN bank_accounts b ON a.bank_account_id = b.id
			WHERE a.user_id = {$this->currentUser->id}
			ORDER BY a.id DESC
			LIMIT {$limit} OFFSET {$offset}
QUERY
		);
		$result->setFetchMode(Enum::FETCH_OBJ);
		while ($payment = $result->fetch()) {
			if ($payment->status == 1) {
				$payment->status = 'Diterima';
			} else if ($payment->status == -1) {
				$payment->status = 'Ditolak';
			} else {
				$payment->status = 'Sedang Diproses';
			}
			$payments[] = $payment;
		}
		$this->_response['status']           = 1;
		$this->_response['data']['payments'] = $payments;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function createAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			$payment                  = new Payment;
			$payment->user_id         = $this->currentUser->id;
			$payment->bank_account_id = BankAccount::findFirstById($this->post->bank_account_id)->id;
			$payment->setPayerBank($this->post->payer_bank);
			$payment->setPayerAccountNumber($this->post->payer_account_number);
			$payment->setAmount($this->post->amount);
			$payment->setStatus(0);
			$payment->created_by = $this->currentUser->id;
			if (!$payment->validation() || !$payment->create()) {
				$errors = new Set;
				foreach ($payment->getMessages() as $error) {
					$errors->add($error->getMessage());
				}
				throw new Exception($errors->join('<br>'));
			}
			$this->_response['status'] = 1;
			throw new Exception('Terima kasih, pembayaran Anda akan Kami proses!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
			return $this->response;
		}
	}
}