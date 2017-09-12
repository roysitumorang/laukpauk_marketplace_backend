<?php

namespace Application\Api\V3\Merchant;

use Application\Models\BankAccount;
use Exception;

class BankAccountsController extends ControllerBase {
	function indexAction() {
		try {
			$bank_accounts = [];
			foreach (BankAccount::find(['published = 1', 'columns' => 'id, bank, number, holder']) as $bank_account) {
				$bank_accounts[] = $bank_account;
			}
			$this->_response['status']                = 1;
			$this->_response['data']['banks']         = BankAccount::BANKS;
			$this->_response['data']['bank_accounts'] = $bank_accounts;
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}