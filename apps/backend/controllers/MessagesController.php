<?php

namespace Application\Backend\Controllers;

use Application\Models\{Message, MessageRecipient};
use Phalcon\Paginator\Adapter\QueryBuilder;

class MessagesController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
				->from(['a' => Message::class])
				->join(MessageRecipient::class, 'a.id = b.message_id', 'b')
				->where('b.user_id = :user_id: AND b.read_at IS NULL', ['user_id' => $this->currentUser->id])
				->orderBy('a.id DESC');
		$paginator    = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page      = $paginator->paginate();
		$pages     = $this->_setPaginationRange($page);
		$messages  = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$messages[] = $item;
		}
		$this->view->menu                  = $this->_menu('Mailbox');
		$this->view->messages              = $messages;
		$this->view->pages                 = $pages;
		$this->view->page                  = $page;
		$this->view->total_messages        = $this->db->fetchColumn("SELECT COUNT(1) FROM message_recipients a JOIN messages b ON a.message_id = b.id WHERE a.user_id = {$this->currentUser->id}");
		$this->view->total_unread_messages = $this->db->fetchColumn("SELECT COUNT(1) FROM message_recipients a JOIN messages b ON a.message_id = b.id WHERE a.user_id = {$this->currentUser->id} AND a.read_at IS NULL");
		$this->view->total_read_messages   = $this->db->fetchColumn("SELECT COUNT(1) FROM message_recipients a JOIN messages b ON a.message_id = b.id WHERE a.user_id = {$this->currentUser->id} AND a.read_at IS NOT NULL");
	}

	function createAction() {
		$message = new Message;
		if ($this->request->isPost()) {
			$message->setSubject($this->request->getPost('subject'));
			$message->setBody($this->request->getPost('body'));
			if ($message->validation() && $message->create()) {
				$this->flashSession->success('Pesan berhasil dikirim.');
				return $this->response->redirect('/admin/messages');
			}
			$this->flashSession->error('Pesan tidak berhasil dikirim, silahkan cek form dan coba lagi.');
			foreach ($message->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu    = $this->_menu('Mailbox');
		$this->view->message = $message;
	}

	function showAction($id) {}

	function deleteAction($id) {}
}