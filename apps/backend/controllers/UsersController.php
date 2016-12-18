<?php

namespace Application\Backend\Controllers;

use Application\Models\Order;
use Application\Models\Role;
use Application\Models\Village;
use Application\Models\User;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UsersController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$status         = User::STATUS;
		$roles          = [];
		$current_status = $this->request->getQuery('status', 'int');
		if ($current_status === null || !array_key_exists($current_status, $status)) {
			$current_status = array_search('ACTIVE', $status);
		}
		$role_items = Role::find([
			'conditions' => 'id > ' . Role::ANONYMOUS,
			'columns'    => 'id, name',
		]);
		foreach ($role_items as $role) {
			$roles[$role->id] = $role;
		}
		$current_role = $this->request->getQuery('role_id', 'int');
		if ($current_role && !array_key_exists($current_role, $roles)) {
			$current_role = null;
		}
		$keyword = $this->request->getQuery('keyword', 'string');
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.email',
				'a.password',
				'a.address',
				'a.village_id',
				'a.mobile_phone',
				'a.status',
				'a.activated_at',
				'a.verified_at',
				'a.activation_token',
				'a.password_reset_token',
				'a.deposit',
				'a.company',
				'a.registration_ip',
				'a.gender',
				'a.date_of_birth',
				'a.avatar',
				'a.thumbnails',
				'a.business_days',
				'a.business_opening_hour',
				'a.business_closing_hour',
				'a.created_by',
				'a.created_at',
				'a.updated_by',
				'a.updated_at',
				'role'                   => 'c.name',
				'village'                => 'd.name',
				'subdistrict'            => 'e.name',
				'total_orders'           => 'COUNT(DISTINCT f.id)',
				'total_pending_orders'   => 'COUNT(DISTINCT g.id)',
				'total_completed_orders' => 'COUNT(DISTINCT h.id)',
				'total_cancelled_orders' => 'COUNT(DISTINCT i.id)',
				'total_products'         => 'COUNT(DISTINCT j.product_id)',
				'total_service_areas'    => 'COUNT(DISTINCT k.id)',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\UserRole', 'a.id = b.user_id', 'b')
			->join('Application\Models\Role', 'b.role_id = c.id', 'c')
			->leftJoin('Application\Models\Village', 'a.village_id = d.id', 'd')
			->leftJoin('Application\Models\Subdistrict', 'd.subdistrict_id = e.id', 'e')
			->leftJoin('Application\Models\Order', 'a.id = IF(c.id = ' . Role::MERCHANT . ', f.merchant_id, f.buyer_id)', 'f')
			->leftJoin('Application\Models\Order', 'a.id = IF(c.id = ' . Role::MERCHANT . ', g.merchant_id, g.buyer_id) AND g.status = ' . array_search('HOLD', Order::STATUS), 'g')
			->leftJoin('Application\Models\Order', 'a.id = IF(c.id = ' . Role::MERCHANT . ', h.merchant_id, h.buyer_id) AND h.status = ' . array_search('COMPLETED', Order::STATUS), 'h')
			->leftJoin('Application\Models\Order', 'a.id = IF(c.id = ' . Role::MERCHANT . ', i.merchant_id, i.buyer_id) AND i.status = ' . array_search('CANCELLED', Order::STATUS), 'i')
			->leftJoin('Application\Models\ProductPrice', 'a.id = j.user_id AND j.published = 1', 'j')
			->leftJoin('Application\Models\ServiceArea', 'a.id = k.user_id', 'k')
			->groupBy('a.id')
			->orderBy('a.id DESC')
			->where('a.status = ' . $current_status);
		if ($current_role) {
			$builder->andWhere('a.role_id = ' . $current_role);
		}
		if ($keyword) {
			$keyword_placeholder = "%{$keyword}%";
			$builder->andWhere('a.name LIKE ?0 OR a.email LIKE ?1 OR phone LIKE ?2', [
				$keyword_placeholder,
				$keyword_placeholder,
				$keyword_placeholder,
			]);
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
			$item->writeAttribute('status', $status[$item->status]);
			if ($item->role == 'Buyer' || $item->role == 'Merchant') {
				$item->writeAttribute('total_pending_bill', $this->db->fetchColumn("SELECT SUM(final_bill) FROM orders WHERE status = 0 AND " . ($item->role == 'Merchant' ? 'merchant_id' : 'buyer_id') . "= {$item->id}"));
				$item->writeAttribute('total_completed_bill', $this->db->fetchColumn("SELECT SUM(final_bill) FROM orders WHERE status = 1 AND " . ($item->role == 'Merchant' ? 'merchant_id' : 'buyer_id') . "= {$item->id}"));
			}
			$users[] = $item;
		}
		$status_hold                       = array_search('HOLD', $status);
		$status_active                     = array_search('ACTIVE', $status);
		$status_suspended                  = array_search('SUSPENDED', $status);
		$this->view->menu                  = $this->_menu('Member');
		$this->view->users                 = $users;
		$this->view->pages                 = $pages;
		$this->view->page                  = $paginator->getPaginate();
		$this->view->status                = $status;
		$this->view->current_status        = $current_status;
		$this->view->roles                 = $roles;
		$this->view->current_role          = $current_role;
		$this->view->keyword               = $keyword;
		$this->view->total_users           = $this->db->fetchColumn('SELECT COUNT(1) FROM users');
		$this->view->total_pending_users   = $this->db->fetchColumn("SELECT COUNT(1) FROM users WHERE `status` = {$status_hold}");
		$this->view->total_active_users    = $this->db->fetchColumn("SELECT COUNT(1) FROM users WHERE `status` = {$status_active}");
		$this->view->total_suspended_users = $this->db->fetchColumn("SELECT COUNT(1) FROM users WHERE `status` = {$status_suspended}");
	}

	function createAction() {
		$user                  = new User;
		$user->deposit         = 0;
		$user->reward          = 0;
		$user->buy_point       = 0;
		$user->affiliate_point = 0;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($user);
			if ($user->validation() && $user->create()) {
				$this->flashSession->success('Penambahan member berhasil.');
				return $this->response->redirect('/admin/users?status=0');
			}
			$this->flashSession->error('Penambahan member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_form_datas($user);
	}

	function showAction() {}

	function updateAction($id) {
		$user = User::findFirst([
			'id = ?0 AND status = ?1',
			'bind' => [
				$id,
				array_search('ACTIVE', User::STATUS),
			],
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
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

	function activateAction($id) {
		$user = User::findFirst([
			'id = ?0 AND status = ?1',
			'bind' => [
				$id,
				array_search('HOLD', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->activate();
		$this->flashSession->success('Aktivasi member berhasil.');
		return $this->response->redirect('/admin/users?status=1#' . $user->id);
	}

	function suspendAction($id) {
		$user = User::findFirst([
			'id = ?0 AND status = ?1',
			'bind' => [
				$id,
				array_search('ACTIVE', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->suspend();
		$this->flashSession->success('Member berhasil dinonaktifkan.');
		return $this->response->redirect('/admin/users?status=-1#' . $user->id);
	}

	function reactivateAction($id) {
		$user = User::findFirst([
			'id = ?0 AND status = ?1',
			'bind' => [
				$id,
				array_search('SUSPENDED', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->reactivate();
		$this->flashSession->success('Member berhasil diaktifkan kembali.');
		return $this->response->redirect('/admin/users?status=1#' . $user->id);
	}

	function verifyAction($id) {
		$user = User::findFirst([
			'conditions' => 'id = ?0 AND status = ?1 AND verified_at IS NULL',
			'bind'       => [
				$id,
				array_search('ACTIVE', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->update(['verified_at' => $this->currentDatetime->format('Y-m-d H:i:s')]);
		$this->flashSession->success('Verifikasi member berhasil.');
		return $this->response->redirect('/admin/users?status=1#' . $user->id);
	}

	private function _prepare_form_datas($user) {
		$subdistricts                 = apcu_fetch('subdistricts');
		$villages                     = apcu_fetch('villages');
		$this->view->menu             = $this->_menu('Members');
		$this->view->roles            = Role::find([
			'id > ' . Role::SUPER_ADMIN,
			'order' => 'name',
		]);
		$this->view->user             = $user;
		$this->view->status           = User::STATUS;
		$this->view->genders          = User::GENDERS;
		$this->view->subdistricts     = $subdistricts;
		$this->view->current_villages = $villages[$user->village->subdistrict->id ?? $subdistricts[0]->id];
		$this->view->villages_json    = json_encode($villages, JSON_NUMERIC_CHECK);
		$this->view->business_days    = User::BUSINESS_DAYS;
	}

	private function _set_model_attributes(&$user) {
		$village_id = $this->request->getPost('village_id', 'int');
		if ($village_id) {
			$user->village = Village::findFirst($village_id);
		}
		$user->setName($this->request->getPost('name'));
		$user->setEmail($this->request->getPost('email'));
		$user->setNewPassword($this->request->getPost('new_password'));
		$user->setNewPasswordConfirmation($this->request->getPost('new_password_confirmation'));
		$user->setAddress($this->request->getPost('address'));
		$user->setMobilePhone($this->request->getPost('mobile_phone'));
		$user->setDeposit($this->request->getPost('deposit'));
		$user->setCompany($this->request->getPost('company'));
		$user->setGender($this->request->getPost('gender'));
		$user->setDateOfBirth($this->request->getPost('date_of_birth'));
		$user->setNewAvatar($_FILES['new_avatar']);
		$user->setBusinessDays($this->request->getPost('business_days'));
		$user->setBusinessOpeningHour($this->request->getPost('business_opening_hour'));
		$user->setBusinessClosingHour($this->request->getPost('business_closing_hour'));
	}
}