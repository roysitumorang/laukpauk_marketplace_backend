<?php

namespace Application\Backend\Controllers;

use Application\Models\BankAccount;
use Phalcon\Paginator\Adapter\Model;

class BankAccountsController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Options');
	}

	function indexAction() {
		$bank_accounts = [];
		$limit         = $this->config->per_page;
		$current_page  = $this->dispatcher->getParam('page', 'int', 1);
		$offset        = ($current_page - 1) * $limit;
		$pagination    = (new Model([
			'data'  => BankAccount::find(['order' => 'bank']),
			'limit' => $limit,
			'page'  => $current_page,
		]))->getPaginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$bank_accounts[] = $item;
		}
		$this->view->bank_accounts = $bank_accounts;
		$this->view->pagination    = $pagination;
		$this->view->pages         = $this->_setPaginationRange($page);
	}

	function createAction() {
		$bank_account = new BankAccount;
		$bank_account->published = 0;
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($bank_account);
			if ($bank_account->validation() && $bank_account->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/bank_accounts');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($bank_account->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->bank_account = $bank_account;
	}

	function updateAction($id) {
		if (!$bank_account = BankAccount::findFirst($id)) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('bank_accounts');
		}
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($bank_account);
			if ($bank_account->validation() && $bank_account->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/bank_accounts');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($bank_account->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->bank_account = $bank_account;
	}

	function toggleStatusAction($id) {
		if ($this->request->isPost()) {
			if (!$bank_account = BankAccount::findFirst($id)) {
				$this->flashSession->error('Data tidak ditemukan.');
			} else {
				$bank_account->update(['published' => $bank_account->published ? 0 : 1]);
			}
		}
		return $this->response->redirect($this->request->getQuery('next'));
	}

	private function _assignModelAttributes(BankAccount &$bank_account) {
		$bank_account->assign($_POST, null, ['bank', 'number', 'holder', 'published']);
	}
}