<?php

namespace Application\Frontend\Controllers;

use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UsersController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$keyword        = str_replace([':', '/'], '', $this->dispatcher->getParam('keyword', 'string'));
		$status         = User::STATUS;
		$current_status = $this->dispatcher->getParam('status', 'int');
		if ($current_status === null || !array_key_exists($current_status, $status)) {
			$current_status = array_search('ACTIVE', $status);
		}
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.address',
				'a.village_id',
				'a.mobile_phone',
				'a.status',
				'a.activated_at',
				'a.activation_token',
				'a.registration_ip',
				'a.gender',
				'a.date_of_birth',
				'a.avatar',
				'a.thumbnails',
				'a.created_by',
				'a.created_at',
				'a.updated_by',
				'a.updated_at',
				'role'                     => 'b.name',
				'village'                  => 'c.name',
				'subdistrict'              => 'd.name',
				'city'                     => 'e.name',
				'province'                 => 'f.name',
				'last_login'               => 'g.sign_in_at',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\Role', 'a.role_id = b.id', 'b')
			->leftJoin('Application\Models\Village', 'a.village_id = c.id', 'c')
			->leftJoin('Application\Models\Subdistrict', 'c.subdistrict_id = d.id', 'd')
			->leftJoin('Application\Models\City', 'd.city_id = e.id', 'e')
			->leftJoin('Application\Models\Province', 'e.province_id = f.id', 'f')
			->leftJoin('Application\Models\LoginHistory', 'a.id = g.user_id', 'g')
			->orderBy('a.id DESC')
			->where('a.merchant_id = ' . $this->currentUser->id)
			->andWhere('a.status = ' . $current_status)
			->andWhere('NOT EXISTS(SELECT 1 FROM Application\Models\LoginHistory h WHERE g.user_id = h.user_id AND h.id > g.id)');
		if ($keyword) {
			$keyword_placeholder = "%{$keyword}%";
			$builder->andWhere('a.name ILIKE ?0 OR a.mobile_phone ILIKE ?1', [
				$keyword_placeholder,
				$keyword_placeholder,
			]);
		}
		$paginator = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->paginate();
		$pages = $this->_setPaginationRange($page);
		$users = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('status', $status[$item->status]);
			$users[] = $item;
		}
		$this->view->menu                  = $this->_menu('Members');
		$this->view->users                 = $users;
		$this->view->pages                 = $pages;
		$this->view->page                  = $paginator->paginate();
		$this->view->status                = $status;
		$this->view->current_status        = $current_status;
		$this->view->keyword               = $keyword;
		$this->view->total_users           = $this->db->fetchColumn("SELECT COUNT(1) FROM users WHERE merchant_id = {$this->currentUser->id}");
		$this->view->total_pending_users   = $this->db->fetchColumn("SELECT COUNT(1) FROM users WHERE merchant_id = {$this->currentUser->id} AND status = 0");
		$this->view->total_active_users    = $this->db->fetchColumn("SELECT COUNT(1) FROM users WHERE merchant_id = {$this->currentUser->id} AND status = 1");
		$this->view->total_suspended_users = $this->db->fetchColumn("SELECT COUNT(1) FROM users WHERE merchant_id = {$this->currentUser->id} AND status = -1");
	}

	function createAction() {
		$user          = new User;
		$user->deposit = 0;
		if ($this->request->isPost()) {
			$user->merchant_id = $this->currentUser->id;
			$user->role_id     = Role::BUYER;
			$this->_set_model_attributes($user);
			if ($user->validation() && $user->create()) {
				$this->flashSession->success('Penambahan member berhasil.');
				return $this->response->redirect('/users?status=0');
			}
			$this->flashSession->error('Penambahan member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_form_datas($user);
	}

	function showAction($id) {
		$user = User::findFirst(['merchant_id = ?0 AND id = ?1 AND status = 1', 'bind' => [$this->currentUser->id, $id]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/users');
		}
		$total = $this->db->fetchOne(<<<QUERY
			SELECT
				COUNT(DISTINCT a.id) AS orders,
				COUNT(DISTINCT b.id) AS pending_orders,
				COUNT(DISTINCT c.id) AS completed_orders,
				COUNT(DISTINCT d.id) AS cancelled_orders
			FROM
				orders a
				LEFT JOIN orders b ON a.buyer_id = b.buyer_id AND b.status = 0
				LEFT JOIN orders c ON a.buyer_id = c.buyer_id AND c.status = 1
				LEFT JOIN orders d ON a.buyer_id = d.buyer_id AND d.status = -1
			WHERE
				a.buyer_id = ?
QUERY
			, Db::FETCH_OBJ, [$user->id]
		);
		if ($user->avatar) {
			$user->thumbnail = $user->getThumbnail(300, 300);
		}
		$this->view->menu       = $this->_menu('Members');
		$this->view->total      = $total;
		$this->view->user       = $user;
		$this->view->status     = User::STATUS;
		$this->view->last_login = LoginHistory::maximum(['user_id = ?0', 'bind' => [$user->id], 'column' => 'sign_in_at']);
	}

	function updateAction($id) {
		$user = User::findFirst(['merchant_id = ?0 AND id = ?1 AND status = 1', 'bind' => [$this->currentUser->id, $id]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/users');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($user);
			if ($user->validation() && $user->update()) {
				$this->flashSession->success('Update member berhasil.');
				return $this->response->redirect("/users/{$user->id}");
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

	function deleteAvatarAction($id) {
		$user = User::findFirst(['merchant_id = ?0 AND id = ?1 AND status = 1', 'bind' => [$this->currentUser->id, $id]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/users');
		}
		$user->deleteAvatar();
		return $this->response->redirect("/users/{$user->id}");
	}

	function activateAction($id) {
		$user = User::findFirst(['merchant_id = ?0 AND id = ?1 AND status = 0', 'bind' => [
			$this->currentUser->id,
			$id,
		]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/users');
		}
		$user->activate();
		$this->flashSession->success('Aktivasi member berhasil.');
		return $this->response->redirect('/users?status=1#' . $user->id);
	}

	function suspendAction($id) {
		$user = User::findFirst(['merchant_id = ?0 AND id = ?1 AND status = 1', 'bind' => [
			$this->currentUser->id,
			$id,
		]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/users');
		}
		$user->suspend();
		$this->flashSession->success('Member berhasil dinonaktifkan.');
		return $this->response->redirect('/users?status=-1#' . $user->id);
	}

	function reactivateAction($id) {
		$user = User::findFirst(['merchant_id = ?0 AND id = ?1 AND status = -1', 'bind' => [
			$this->currentUser->id,
			$id,
		]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/users');
		}
		$user->reactivate();
		$this->flashSession->success('Member berhasil diaktifkan kembali.');
		return $this->response->redirect('/users?status=1#' . $user->id);
	}

	private function _prepare_form_datas(User $user) {
		$provinces    = [];
		$cities       = [];
		$subdistricts = [];
		$villages     = [];
		$result       = $this->db->query(<<<QUERY
			SELECT
				a.id AS province_id,
				a.name AS province_name,
				b.id AS city_id,
				CONCAT_WS(' ', b.type, b.name) AS city_name,
				c.id AS subdistrict_id,
				c.name AS subdistrict_name,
				d.id AS village_id,
				d.name AS village_name
			FROM provinces a
			JOIN cities b ON a.id = b.province_id
			JOIN subdistricts c ON b.id = c.city_id
			JOIN villages d ON c.id = d.subdistrict_id
			ORDER BY province_name, city_name, subdistrict_name, village_name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$provinces[$row->province_id] = $row->province_name;
			if (!isset($cities[$row->province_id])) {
				$cities[$row->province_id] = [];
			}
			if (!isset($cities[$row->province_id][$row->city_id])) {
				$cities[$row->province_id][$row->city_id] = $row->city_name;
			}
			if (!isset($subdistricts[$row->city_id])) {
				$subdistricts[$row->city_id] = [];
			}
			if (!isset($subdistricts[$row->city_id][$row->subdistrict_id])) {
				$subdistricts[$row->city_id][$row->subdistrict_id] = $row->subdistrict_name;
			}
			if (!isset($villages[$row->subdistrict_id])) {
				$villages[$row->subdistrict_id] = [];
			}
			if (!isset($villages[$row->subdistrict_id][$row->village_id])) {
				$villages[$row->subdistrict_id][$row->village_id] = $row->village_name;
			}
		}
		$current_province_id              = $user->village->subdistrict->city->province->id ?? array_keys($provinces)[0];
		$current_cities                   = $cities[$current_province_id];
		$current_city_id                  = $user->village->subdistrict->city->id ?? array_keys($current_cities)[0];
		$current_subdistricts             = $subdistricts[$current_city_id];
		$current_subdistrict_id           = $user->village->subdistrict->id ?? array_keys($current_subdistricts)[0];
		$current_villages                 = $villages[$current_subdistrict_id];
		$this->view->menu                 = $this->_menu('Members');
		$this->view->user                 = $user;
		$this->view->status               = User::STATUS;
		$this->view->genders              = User::GENDERS;
		$this->view->provinces            = $provinces;
		$this->view->cities               = $cities;
		$this->view->subdistricts         = $subdistricts;
		$this->view->villages             = $villages;
		$this->view->current_cities       = $current_cities;
		$this->view->current_subdistricts = $current_subdistricts;
		$this->view->current_villages     = $current_villages;
	}

	private function _set_model_attributes(&$user) {
		$village_id = $this->request->getPost('village_id', 'int');
		if ($village_id) {
			$user->village = Village::findFirst($village_id);
		}
		$user->setName($this->request->getPost('name'));
		$user->setNewPassword($this->request->getPost('new_password'));
		$user->setNewPasswordConfirmation($this->request->getPost('new_password_confirmation'));
		$user->setAddress($this->request->getPost('address'));
		$user->setMobilePhone($this->request->getPost('mobile_phone'));
		$user->setGender($this->request->getPost('gender'));
		$user->setDateOfBirth($this->request->getPost('date_of_birth'));
		$user->setNewAvatar($_FILES['new_avatar']);
	}
}