<?php

namespace Application\Backend\Controllers;

use Application\Models\Sms;
use Application\Models\User;
use Phalcon\Paginator\Adapter\Model;

class SmsController extends ControllerBase {
	function initialize() {
		parent::initialize();
		$this->view->menu  = $this->_menu('Mailbox');
		$this->view->users = User::find(['premium_merchant IS NULL AND merchant_id IS NULL AND status = 1', 'order' => 'name ASC']);
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => Sms::find(['order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		$texts = [];
		foreach ($page->items as $item) {
			$recipients = '';
			if ($this->db->fetchColumn("SELECT COUNT(1) FROM sms_recipient WHERE sms_id = {$item->id}") > 1) {
				$recipients .= 'Semua Member';
			} else {
				$i      = 0;
				$result = $this->db->query("SELECT a.name FROM users a JOIN sms_recipient b ON a.id = b.user_id WHERE b.sms_id = {$item->id} ORDER BY a.name");
				$result->setFetchMode(Db::FETCH_OBJ);
				while ($row = $result->fetch()) {
					$recipients .= ($i++ ? ', ' : '') . ' ' . $row->name;
				}
			}
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('recipients', $recipients);
			$texts[] = $item;
		}
		$this->view->texts = $texts;
		$this->view->pages = $pages;
		$this->view->page  = $paginator->getPaginate();
	}

	function createAction() {
		$sms     = new Sms;
		$user_id = '';
		if ($this->request->isPost()) {
			$recipients = [];
			$user_id    = $this->request->getPost('user_id');
			$sms->setBody($this->request->getPost('body'));
			if ($user_id && $recipient = User::findFirst(['id = ?0 AND premium_merchant IS NULL AND merchant_id IS NULL', 'bind' => [$user_id]])) {
				$recipients[] = $recipient;
			} else {
				$recipients = $this->view->users;
			}
			$sms->recipients = $recipients;
			if ($sms->validation() && $sms->send()) {
				$this->flashSession->success('SMS berhasil dikirim.');
				return $this->response->redirect('/admin/sms');
			}
			$this->flashSession->error('SMS tidak terkirim, silahkan cek form dan coba lagi.');
			foreach ($sms->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->sms     = $sms;
		$this->view->user_id = $user_id;
	}
}