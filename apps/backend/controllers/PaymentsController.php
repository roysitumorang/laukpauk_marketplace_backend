<?php

namespace Application\Backend\Controllers;

use Application\Models\Payment;
use DateTime;
use IntlDateFormatter;
use Phalcon\Paginator\Adapter\Model;

class PaymentsController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Options');
	}

	function indexAction() {
		$datetime_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'd MMM yyyy HH.mm'
		);
		$all_status     = array_keys(Payment::STATUS);
		$current_status = $this->dispatcher->getParam('status', 'int');
		$keyword        = $this->dispatcher->getParam('keyword');
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$params         = ['1 = 1', 'bind' => [], 'order' => 'id DESC'];
		if (is_int($current_status) && in_array($current_status, $all_status)) {
			$params[0] .= " AND status = {$current_status}";
		}
		if ($keyword) {
			$search_query = "%{$keyword}%";
			$params[0]   .= ' AND (code ILIKE ?0 OR EXISTS(SELECT 1 FROM Application\Models\User WHERE user_id = Application\Models\User.id AND Application\Models\User.company ILIKE ?1))';
			for ($i = 0; $i < 2; $i++) {
				$params['bind'][] = $search_query;
			}
		}
		$paginator = new Model([
			'data'  => Payment::find($params),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page     = $paginator->getPaginate();
		$pages    = $this->_setPaginationRange($page);
		$payments = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('created_at', $datetime_formatter->format(new DateTime($item->created_at, $this->currentDatetime->getTimezone())));
			$payments[] = $item;
		}
		$this->view->payments                = $payments;
		$this->view->page                    = $page;
		$this->view->pages                   = $pages;
		$this->view->all_status              = Payment::STATUS;
		$this->view->current_status          = $current_status;
		$this->view->keyword                 = $keyword;
		$this->view->total_payments          = Payment::count();
		$this->view->total_pending_payments  = Payment::count('status = 0');
		$this->view->total_approved_payments = Payment::count('status = 1');
		$this->view->total_rejected_payments = Payment::count('status = -1');
		$this->view->next                    = '/admin/payments/index' . ($current_status ? '/status:' . $current_status : '') . ($keyword ? '/keyword:' . $keyword : '') . ($current_page > 1 ? '/page:' . $current_page : '');
	}

	function approveAction($id) {
		if ($this->request->isPost()) {
			if (!$payment = Payment::findFirst(['id = ?0 AND status = 0', 'bind' => [$id]])) {
				$this->flashSession->error('Data tidak ditemukan.');
			} else {
				$payment->approve();
				$this->flashSession->success('Pembayaran telah diterima');
			}
		}
		return $this->response->redirect($this->request->getPost('next'));
	}

	function rejectAction($id) {
		if ($this->request->isPost()) {
			if (!$payment = Payment::findFirst(['id = ?0 AND status = 0', 'bind' => [$id]])) {
				$this->flashSession->error('Data tidak ditemukan.');
			} else {
				$payment->reject();
				$this->flashSession->success('Pembayaran telah ditolak');
			}
		}
		return $this->response->redirect($this->request->getPost('next'));
	}
}