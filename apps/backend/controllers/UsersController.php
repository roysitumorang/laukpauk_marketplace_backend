<?php

namespace Application\Backend\Controllers;

use Application\Models\Order;
use Application\Models\Role;
use Application\Models\Village;
use Application\Models\User;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UsersController extends BaseController {
	function indexAction() {
		$limit             = $this->config->per_page;
		$current_page      = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset            = ($current_page - 1) * $limit;
		$status            = User::STATUS;
		$roles             = [];
		$current_status    = $this->request->getQuery('status', 'int');
		if ($current_status === null || !array_key_exists($current_status, $status)) {
			$current_status = array_search('ACTIVE', $status);
		}
		$role_items        = Role::find([
			'conditions' => 'id > ' . Role::ANONYMOUS,
			'columns'    => 'id, name',
		]);
		foreach ($role_items as $role) {
			$roles[$role->id] = $role;
		}
		$current_role      = $this->request->getQuery('role_id', 'int');
		if ($current_role && !array_key_exists($current_role, $roles)) {
			$current_role = null;
		}
		$keyword           = $this->request->getQuery('keyword', 'string');
		$builder           = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.role_id',
				'a.name',
				'a.email',
				'a.password',
				'a.address',
				'a.village_id',
				'a.phone',
				'a.mobile',
				'a.premium',
				'a.affiliate_link',
				'a.status',
				'a.activated_at',
				'a.verified_at',
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
				'role'                   => 'b.name',
				'village'                => 'c.name',
				'subdistrict'            => 'd.name',
				'total_orders'           => 'COUNT(DISTINCT e.id)',
				'total_pending_orders'   => 'COUNT(DISTINCT f.id)',
				'total_completed_orders' => 'COUNT(DISTINCT f.id)',
				'total_cancelled_orders' => 'COUNT(DISTINCT h.id)',
				'total_products'         => 'COUNT(DISTINCT i.product_id)',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\Role', 'a.role_id = b.id', 'b')
			->leftJoin('Application\Models\Village', 'a.village_id = c.id', 'c')
			->leftJoin('Application\Models\Subdistrict', 'c.subdistrict_id = d.id', 'd')
			->leftJoin('Application\Models\Order', 'a.id = IF(a.role_id = ' . Role::MERCHANT . ', e.merchant_id, e.buyer_id)', 'e')
			->leftJoin('Application\Models\Order', 'a.id = IF(a.role_id = ' . Role::MERCHANT . ', f.merchant_id, f.buyer_id) AND f.status = ' . array_search('HOLD', Order::STATUS), 'f')
			->leftJoin('Application\Models\Order', 'a.id = IF(a.role_id = ' . Role::MERCHANT . ', g.merchant_id, g.buyer_id) AND g.status = ' . array_search('COMPLETED', Order::STATUS), 'g')
			->leftJoin('Application\Models\Order', 'a.id = IF(a.role_id = ' . Role::MERCHANT . ', h.merchant_id, h.buyer_id) AND h.status = ' . array_search('CANCELLED', Order::STATUS), 'h')
			->leftJoin('Application\Models\ProductPrice', 'a.role_id = ' . Role::MERCHANT . ' AND a.id = i.user_id AND i.published = 1', 'i')
			->groupBy('a.id')
			->orderBy('a.id DESC');
		$builder->where('a.status = ' . $current_status);
		if ($current_role) {
			$builder->andWhere('a.role_id = ' . $current_role);
		}
		if ($keyword) {
			$keyword_placeholder = "%{$keyword}%";
			$builder->andWhere('a.name LIKE ?1 OR a.email LIKE ?2 OR phone LIKE ?3', [
				1 => $keyword_placeholder,
				2 => $keyword_placeholder,
				3 => $keyword_placeholder,
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
		$user->subdistrict_id  = null;
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
			$user->subdistrict_id = $this->request->getPost('subdistrict_id', 'int');
		}
		$this->_prepare_form_datas($user);
	}

	function showAction() {}

	function updateAction($id) {
		$user = User::findFirst([
			'conditions' => 'id = ?1 AND status = ?2',
			'bind'       => [
				1 => $id,
				2 => array_search('ACTIVE', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->subdistrict_id = null;
		if ($user->village_id) {
			$village              = Village::findFirst($user->village_id);
			$user->subdistrict_id = $village->subdistrict->id;
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
		$status_hold   = array_search('HOLD', User::STATUS);
		$status_active = array_search('ACTIVE', User::STATUS);
		$user          = User::findFirst([
			'conditions' => 'id = ?1 AND status = ?2',
			'bind'       => [
				1 => $id,
				2 => $status_hold,
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->update([
			'status'           => $status_active,
			'activation_token' => null,
			'activated_at'     => $this->currentDatetime->format('Y-m-d H:i:s'),
		]);
		$this->flashSession->success('Aktivasi member berhasil.');
		return $this->response->redirect($this->request->getQuery('next'));
	}

	function suspendAction($id) {
		$status_active    = array_search('ACTIVE', User::STATUS);
		$status_suspended = array_search('SUSPENDED', User::STATUS);
		$user             = User::findFirst([
			'conditions' => 'id = ?1 AND status = ?2',
			'bind'       => [
				1 => $id,
				2 => $status_active,
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->update(['status' => $status_suspended]);
		$this->flashSession->success('Member berhasil dinonaktifkan.');
		return $this->response->redirect($this->request->getQuery('next'));
	}

	function reactivateAction($id) {
		$status_suspended = array_search('SUSPENDED', User::STATUS);
		$status_active    = array_search('ACTIVE', User::STATUS);
		$user             = User::findFirst([
			'conditions' => 'id = ?1 AND status = ?2',
			'bind'       => [
				1 => $id,
				2 => $status_suspended,
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->update(['status' => $status_active]);
		$this->flashSession->success('Member berhasil diaktifkan kembali.');
		return $this->response->redirect($this->request->getQuery('next'));
	}

	function verifyAction($id) {
		$user = User::findFirst([
			'conditions' => 'id = ?1 AND status = ?2 AND verified_at IS NULL',
			'bind'       => [
				1 => $id,
				2 => array_search('ACTIVE', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->update(['verified_at' => $this->currentDatetime->format('Y-m-d H:i:s')]);
		$this->flashSession->success('Verifikasi member berhasil.');
		return $this->response->redirect($this->request->getQuery('next'));
	}

	private function _prepare_form_datas($user) {
		$subdistricts              = apcu_fetch('subdistricts');
		$villages                  = apcu_fetch('villages');
		$this->view->menu          = $this->_menu('Members');
		$this->view->user          = $user;
		$this->view->status        = User::STATUS;
		$this->view->genders       = User::GENDERS;
		$this->view->memberships   = User::MEMBERSHIPS;
		$this->view->subdistricts  = $subdistricts;
		$this->view->villages      = $villages[$user->subdistrict_id ?? $subdistricts[0]->id];
		$this->view->villages_json = json_encode($villages, JSON_NUMERIC_CHECK);
	}

	private function _set_model_attributes(&$user) {
		$user->role = Role::findFirst($this->request->getPost('role_id', 'int') ?: Role::MERCHANT);
		$user->setName($this->request->getPost('name'));
		$user->setEmail($this->request->getPost('email'));
		$user->setNewPassword($this->request->getPost('new_password'));
		$user->setNewPasswordConfirmation($this->request->getPost('new_password_confirmation'));
		$user->setAddress($this->request->getPost('address'));
		$user->setVillageId($this->request->getPost('village_id'));
		$user->setPhone($this->request->getPost('phone'));
		$user->setMobile($this->request->getPost('mobile'));
		$user->setPremium($this->request->getPost('premium'));
		$user->setAffiliateLink($this->request->getPost('affiliate_link'));
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