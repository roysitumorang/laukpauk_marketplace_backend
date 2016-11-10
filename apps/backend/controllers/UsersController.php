<?php

namespace Application\Backend\Controllers;

use Application\Models\City;
use Application\Models\Province;
use Application\Models\Role;
use Application\Models\Subdistrict;
use Application\Models\User;
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
		$user = new User;
		if ($this->request->isPost()) {
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
			$user->SetAffiliateLink($this->request->getPost('affiliate_link'));
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
			if ($user->validation() && $user->create()) {
				$this->flashSession->success('Penambahan member berhasil.');
				return $this->response->redirect('/admin/users');
			}
			$this->flashSession->error('Penambahan member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$provinces                = Province::find();
		$cities                   = City::findByProvinceId($user->province_id ?? $provinces->getFirst()->id);
		$subdistricts             = Subdistrict::findByCityId($user->city_id ?? $cities->getFirst()->id);
		$this->view->menu         = $this->_menu('Members');
		$this->view->user         = $user;
		$this->view->status       = User::STATUS;
		$this->view->genders      = User::GENDERS;
		$this->view->memberships  = User::MEMBERSHIPS;
		$this->view->provinces    = $provinces;
		$this->view->cities       = $cities;
		$this->view->subdistricts = $subdistricts;
		$this->view->regions      = json_encode(apcu_fetch('provinces'), JSON_NUMERIC_CHECK);
	}

	function showAction() {}

	function updateAction() {}

	function activateAction() {}

	function deactivateAction() {}

	function verifyAction() {}

	function unverifyAction() {}
}