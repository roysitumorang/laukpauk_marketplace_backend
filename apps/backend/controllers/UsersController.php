<?php

namespace Application\Backend\Controllers;
use Application\Models\User;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class UsersController extends BaseController {
	function indexAction() {
		$search_options = [
			'name'         => 'Nama',
			'email'        => 'Email',
			'city'         => 'Kota',
			'zip_code'     => 'Kode Pos',
			'role'         => 'Role',
			'created_at'   => 'Tanggal Pendaftaran (YYYY-mm-dd)',
			'activated_at' => 'Tanggal Aktivasi (YYYY-mm-dd)',
			'status'       => 'Status',
		];
		$current_page   = filter_var($this->request->get('page'), FILTER_VALIDATE_INT) ?? 1;
		$users          = User::find();
		$limit          = 10;
		$paginator      = new PaginatorModel([
			'data'  => $users,
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$this->view->menu                = $this->_menu('Member');
		$this->view->page                = $paginator->getPaginate();
		$this->view->multi_page          = count($users) / $limit > 1;
		$this->view->search_options      = $search_options;
		$based_on                        = $this->request->get('based_on');
		$this->view->based_on            = array_key_exists($based_on, $search_options) ? $based_on : array_keys($search_options)[0];
		$this->view->hold                = User::STATUS_HOLD;
		$this->view->active              = User::STATUS_ACTIVE;
		$this->view->total_user          = $this->db->fetchColumn('SELECT COUNT(1) FROM users');
		$this->view->total_active_users  = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE `status` = ?', [User::STATUS_ACTIVE]);
		$this->view->total_pending_users = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE `status` = ?', [User::STATUS_HOLD]);
	}

	function newAction() {}

	function createAction() {}

	function showAction() {}

	function editAction() {}

	function updateAction() {}

	function activateAction() {}

	function deactivateAction() {}

	function verifyAction() {}

	function unverifyAction() {}
}
