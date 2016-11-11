<?php

namespace Application\Backend\Controllers;

use Application\Models\Role;
use Application\Models\User;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class UsersController extends BaseController {
	function indexAction() {
		$parameter          = $this->request->get('parameter');
		$keyword            = $this->request->get('keyword');
		$search_parameters  = [
			'name'         => 'Nama',
			'email'        => 'Email',
			'city'         => 'Kota',
			'zip_code'     => 'Kode Pos',
			'role'         => 'Role',
			'created_at'   => 'Tanggal Pendaftaran (YYYY-mm-dd)',
			'activated_at' => 'Tanggal Aktivasi (YYYY-mm-dd)',
			'status'       => 'Status',
		];
		$current_page       = filter_var($this->request->get('page'), FILTER_VALIDATE_INT) ?? 1;
		$users              = User::find();
		$limit              = 10;
		$paginator          = new PaginatorModel([
			'data'  => $users,
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$this->view->menu                = $this->_menu('Member');
		$this->view->page                = $paginator->getPaginate();
		$this->view->multi_page          = count($users) / $limit > 1;
		$this->view->search_parameters   = $search_parameters;
		$this->view->parameter           = array_key_exists($parameter, $search_options) ? $parameter : array_keys($search_parameters)[0];
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