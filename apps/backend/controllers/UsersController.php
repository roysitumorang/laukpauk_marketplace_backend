<?php

namespace Application\Backend\Controllers;

use Application\Models\Role;
use Application\Models\User;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UsersController extends BaseController {
	function indexAction() {
		$search_parameters = [
			'name'         => 'Nama',
			'email'        => 'Email',
			'city'         => 'Kota',
			'zip_code'     => 'Kode Pos',
			'role'         => 'Role',
			'created_at'   => 'Tanggal Pendaftaran (YYYY-mm-dd)',
			'activated_at' => 'Tanggal Aktivasi (YYYY-mm-dd)',
			'status'       => 'Status',
		];
		$limit             = $this->config->per_page;
		$current_page      = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset            = ($current_page - 1) * $limit;
		$parameter         = $this->request->get('parameter', 'string');
		$keyword           = $this->request->getQuery('keyword', 'string');
		$builder           = $this->modelsManager->createBuilder()
			->columns([
				'id' => 'a.id',
				'a.role_id',
				'a.name',
				'a.email',
				'a.password',
				'a.address',
				'a.zip_code',
				'a.subdistrict_id',
				'a.phone',
				'a.mobile',
				'a.premium',
				'a.affiliate_link',
				'a.status',
				'a.activated_at',
				'a.activation_token',
				'a.password_reset_token',
				'a.last_seen',
				'a.deposit',
				'a.ktp',
				'a.company',
				'a.npwp',
				'a.registration_ip',
				'a.twitter_id',
				'a.google_id',
				'a.facebook_id',
				'a.reward',
				'a.gender',
				'a.date_of_birth',
				'a.buy_point',
				'a.affiliate_point',
				'a.avatar',
				'a.thumbnails',
				'a.created_by',
				'a.created_at',
				'a.updated_by',
				'a.updated_at',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\Role', 'a.role_id = b.id', 'b')
			->leftJoin('Application\Models\Subdistrict', 'a.subdistrict_id = c.id', 'c')
			->leftJoin('Application\Models\City', 'c.city_id = d.id', 'd')
			->orderBy('id DESC');
		if ($keyword) {
			if ($parameter == 'name') {
				$builder->where('a.name LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			} else if ($parameter == 'email') {
				$builder->where('a.email LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			} else if ($parameter == 'city') {
				$builder->where('d.name LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			} else if ($parameter == 'zip_code') {
				$builder->where('a.zip_code LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			} else if ($parameter == 'role') {
				$builder->where('b.name LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			} else if ($parameter == 'created_at') {
				$builder->where('a.created_at LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			} else if ($parameter == 'activated_at') {
				$builder->where('a.activated_at LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			} else if ($parameter == 'status') {
				$builder->where('a.status = :keyword:', ['keyword' => $keyword]);
			}
		}
		$paginator = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page      = $paginator->getPaginate();
		$pages     = $this->_setPaginationRange($page);
		$users     = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$users[] = $item;
		}
		$this->view->menu                = $this->_menu('Member');
		$this->view->users               = $users;
		$this->view->page                = $paginator->getPaginate();
		$this->view->multi_page          = count($users) / $limit > 1;
		$this->view->search_parameters   = $search_parameters;
		$this->view->parameter           = $parameter;
		$this->view->keyword             = $keyword;
		$this->view->hold                = User::STATUS['HOLD'];
		$this->view->active              = User::STATUS['ACTIVE'];
		$this->view->total_users         = $this->db->fetchColumn('SELECT COUNT(1) FROM users');
		$this->view->total_active_users  = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE `status` = ?', [User::STATUS['ACTIVE']]);
		$this->view->total_pending_users = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE `status` = ?', [User::STATUS['HOLD']]);
	}

	function createAction() {
		$user                  = new User;
		$user->deposit         = 0;
		$user->reward          = 0;
		$user->buy_point       = 0;
		$user->affiliate_point = 0;
		$user->city_id         = null;
		$user->province_id     = null;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($user);
			if ($user->validation() && $user->create()) {
				$this->flashSession->success('Penambahan member berhasil.');
				return $this->response->redirect('/admin/users');
			}
			$this->flashSession->error('Penambahan member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
			$user->province_id = $this->request->getPost('province_id', 'int');
			$user->city_id     = $this->request->getPost('city_id', 'int');
		}
		$this->_prepare_form_datas($user);
	}

	function showAction() {}

	function updateAction($id) {
		if (!$user = User::findFirst($id)) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('users');
		}
		$user->province_id = null;
		$user->city_id     = null;
		if ($user->subdistrict_id) {
			$city              = $this->db->fetchOne("SELECT b.id, b.province_id FROM subdistricts a JOIN cities b ON a.city_id = b.id WHERE a.id = {$user->subdistrict_id}", Db::FETCH_OBJ);
			$user->province_id = $city->province_id;
			$user->city_id     = $city->id;
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('delete_avatar')) {
				$user->deleteAvatar();
				return $this->response->redirect("/admin/users/update/{$user->id}");
			}
			$this->_set_model_attributes($user);
			if ($user->validation() && $user->update()) {
				$this->flashSession->success('Update member berhasil.');
				return $this->response->redirect('/admin/users');
			}
			$this->flashSession->error('Update member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
			$user->province_id = $this->request->getPost('province_id', 'int');
			$user->city_id     = $this->request->getPost('city_id', 'int');
		}
		$this->_prepare_form_datas($user);
	}

	function activateAction() {}

	function suspendAction() {}

	private function _prepare_form_datas($user) {
		$provinces                     = apcu_fetch('provinces');
		$cities                        = apcu_fetch('cities');
		$subdistricts                  = apcu_fetch('subdistricts');
		$this->view->menu              = $this->_menu('Members');
		$this->view->user              = $user;
		$this->view->status            = User::STATUS;
		$this->view->genders           = User::GENDERS;
		$this->view->memberships       = User::MEMBERSHIPS;
		$this->view->provinces         = $provinces;
		$this->view->cities            = $cities[$user->province_id ?? $provinces[0]->id];
		$this->view->subdistricts      = $subdistricts[$user->city_id ?? $cities[$provinces[0]->id][0]->id];
		$this->view->provinces_json    = json_encode($provinces, JSON_NUMERIC_CHECK);
		$this->view->cities_json       = json_encode($cities, JSON_NUMERIC_CHECK);
		$this->view->subdistricts_json = json_encode($subdistricts, JSON_NUMERIC_CHECK);
	}

	private function _set_model_attributes(&$user) {
		$user->role = Role::findFirst($this->request->getPost('role_id', 'int') ?: Role::MERCHANT);
		$user->setName($this->request->getPost('name'));
		$user->setEmail($this->request->getPost('email'));
		$user->setNewPassword($this->request->getPost('new_password'));
		$user->setNewPasswordConfirmation($this->request->getPost('new_password_confirmation'));
		$user->setAddress($this->request->getPost('address'));
		$user->setZipCode($this->request->getPost('zip_code'));
		$user->setSubdistrictId($this->request->getPost('subdistrict_id'));
		$user->setPhone($this->request->getPost('phone'));
		$user->setMobile($this->request->getPost('mobile'));
		$user->setPremium($this->request->getPost('premium'));
		$user->setAffiliateLink($this->request->getPost('affiliate_link'));
		$user->setStatus($this->request->getPost('status'));
		$user->setDeposit($this->request->getPost('deposit'));
		$user->setKtp($this->request->getPost('ktp'));
		$user->setCompany($this->request->getPost('company'));
		$user->setNpwp($this->request->getPost('npwp'));
		$user->setReward($this->request->getPost('reward'));
		$user->setGender($this->request->getPost('gender'));
		$user->setDateOfBirth($this->request->getPost('date_of_birth'));
		$user->setBuyPoint($this->request->getPost('buy_point'));
		$user->setAffiliatePoint($this->request->getPost('affiliate_point'));
		$user->setNewAvatar($_FILES['avatar']);
	}
}