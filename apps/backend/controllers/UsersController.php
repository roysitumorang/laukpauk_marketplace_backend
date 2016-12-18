<?php

namespace Application\Backend\Controllers;

use Application\Models\Role;
use Application\Models\Village;
use Application\Models\User;
use Application\Models\UserRole;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UsersController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$status         = User::STATUS;
		$current_status = $this->request->getQuery('status', 'int');
		if ($current_status === null || !array_key_exists($current_status, $status)) {
			$current_status = array_search('ACTIVE', $status);
		}
		$current_role = $this->request->getQuery('role_id', 'int');
		$keyword      = $this->request->getQuery('keyword', 'string');
		$builder      = $this->modelsManager->createBuilder()
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
				'village'                  => 'c.name',
				'subdistrict'              => 'd.name'
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\UserRole', 'a.id = b.user_id', 'b')
			->leftJoin('Application\Models\Village', 'a.village_id = c.id', 'c')
			->leftJoin('Application\Models\Subdistrict', 'c.subdistrict_id = d.id', 'd')
			->groupBy('a.id')
			->orderBy('a.id DESC')
			->where('a.status = ' . $current_status);
		if ($current_role) {
			$builder->andWhere('b.role_id = ' . $current_role);
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
			$roles = [];
			foreach ($this->db->fetchAll('SELECT b.name FROM user_role a JOIN roles b ON a.role_id = b.id WHERE a.user_id = ?', Db::FETCH_OBJ, [$item->id]) as $role) {
				$roles[] = $role->name;
			}
			$item->writeAttribute('roles', $roles);
			$users[] = $item;
		}
		$this->view->menu                  = $this->_menu('Member');
		$this->view->users                 = $users;
		$this->view->pages                 = $pages;
		$this->view->page                  = $paginator->getPaginate();
		$this->view->status                = $status;
		$this->view->current_status        = $current_status;
		$this->view->roles                 = Role::find(['id > 1', 'order' => 'name']);
		$this->view->current_role          = $current_role;
		$this->view->keyword               = $keyword;
		$this->view->total_users           = $this->db->fetchColumn('SELECT COUNT(1) FROM users');
		$this->view->total_pending_users   = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE `status` = 0');
		$this->view->total_active_users    = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE `status` = 1');
		$this->view->total_suspended_users = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE `status` = -1');
	}

	function createAction() {
		$user          = new User;
		$user->deposit = 0;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($user);
			if ($user->validation() && $user->create()) {
				$this->_save_roles($user);
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

	function showAction($id) {
		$user  = User::findFirst(['id = ?0 AND status = 1', 'bind' => [$id]]);
		$roles = [];
		foreach ($user->getRelated('roles') as $role) {
			$roles[$role->name] = 1;
		}
		if (isset($roles['Buyer'])) {
			$column = 'buyer_id';
		} else if (isset($roles['Merchant'])) {
			$column                    = 'merchant_id';
			$this->view->products      = $this->db->fetchColumn('SELECT COUNT(1) FROM product_prices WHERE user_id = ?', [$user->id]);
			$this->view->service_areas = $this->db->fetchColumn('SELECT COUNT(1) FROM service_areas WHERE user_id = ?', [$user->id]);
		}
		if ($column) {
			$total = $this->db->fetchOne("
				SELECT
					COUNT(DISTINCT a.id) AS orders,
					COUNT(DISTINCT b.id) AS pending_orders,
					COUNT(DISTINCT c.id) AS completed_orders,
					COUNT(DISTINCT d.id) AS cancelled_orders
				FROM
					orders a
					LEFT JOIN orders b ON a.{$column} = b.{$column} AND b.status = 0
					LEFT JOIN orders c ON a.{$column} = c.{$column} AND c.status = 1
					LEFT JOIN orders d ON a.{$column} = d.{$column} AND d.status = -1
				WHERE
					a.{$column} = ?", Db::FETCH_OBJ, [$user->id]);
			$this->view->total = $total;
		}
		$this->view->menu   = $this->_menu('Members');
		$this->view->user   = $user;
		$this->view->status = User::STATUS;
		$this->view->roles  = $roles;
	}

	function updateAction($id) {
		$user = User::findFirst(['id = ?0 AND status = 1', 'bind' => [$id]]);
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
				$this->_save_roles($user);
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
		$this->view->roles            = Role::find(['id > 1', 'order' => 'name']);
		$this->view->user             = $user;
		$this->view->status           = User::STATUS;
		$this->view->genders          = User::GENDERS;
		$this->view->subdistricts     = $subdistricts;
		$this->view->current_villages = $villages[$user->village->subdistrict->id ?? $subdistricts[0]->id];
		$this->view->villages_json    = json_encode($villages, JSON_NUMERIC_CHECK);
		$this->view->business_days    = User::BUSINESS_DAYS;
		if ($user->id && !isset($this->view->role_ids)) {
			$role_ids = [];
			foreach ($user->getRelated('roles') as $role) {
				$role_ids[] = $role->id;
			}
			$this->view->role_ids = $role_ids;
		}
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
		$user->setCompany($this->request->getPost('company'));
		$user->setGender($this->request->getPost('gender'));
		$user->setDateOfBirth($this->request->getPost('date_of_birth'));
		$user->setNewAvatar($_FILES['new_avatar']);
		$user->setBusinessDays($this->request->getPost('business_days'));
		$user->setBusinessOpeningHour($this->request->getPost('business_opening_hour'));
		$user->setBusinessClosingHour($this->request->getPost('business_closing_hour'));
		$this->view->role_ids = $this->request->getPost('role_id');
	}

	private function _save_roles(User $user) {
		$role_ids = $this->request->getPost('role_id');
		if ($role_ids) {
			UserRole::find([
				'user_id = :user_id: AND role_id NOT IN({role_ids:array})',
				'bind' => [
					'user_id'  => $user->id,
					'role_ids' => $role_ids,
				],
			])->delete();
			foreach ($role_ids as $role_id) {
				$role = Role::findFirst(['id > 1 AND id = ?0', 'bind' => [$role_id]]);
				if ($role && !UserRole::findFirst(['user_id = ?0 AND role_id = ?1', 'bind' => [$user->id, $role->id]])) {
					$user_role = new UserRole(['user_id' => $user->id, 'role_id' => $role->id]);
					$user_role->create();
				}
			}
		}
	}
}