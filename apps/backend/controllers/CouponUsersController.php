<?php

namespace Application\Backend\Controllers;

use Application\Models\Coupon;
use Application\Models\CouponUser;
use Application\Models\Role;
use Ds\Map;
use Phalcon\Paginator\Adapter\QueryBuilder;

class CouponUsersController extends ControllerBase {
	private $_coupon, $_current_page, $_query_string;

	function initialize() {
		parent::initialize();
		if (!($coupon_id = $this->dispatcher->getParam('coupon_id')) || !($this->_coupon = Coupon::findFirstById($coupon_id))) {
			$this->flashSession->error('Kupon tidak ditemukan.');
			$this->response->redirect('coupons');
			return false;
		}
	}

	function indexAction() {
		$offset = 0;
		$page   = $this->_prepare_datas($offset);
		$pages  = $this->_setPaginationRange($page);
		$users  = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$users[] = $item;
		}
		$this->view->menu   = $this->_menu('Products');
		$this->view->coupon = $this->_coupon;
		$this->view->users  = $users;
		$this->view->page   = $page;
		$this->view->pages  = $pages;
	}

	function createAction() {
		if ($this->request->isPost()) {
			$offset = 0;
			$page = $this->_prepare_datas($offset);
			foreach ($page->items as $user) {
				$coupon_user = CouponUser::findFirst(['coupon_id = ?0 AND user_id = ?1', 'bind' => [$this->_coupon->id, $user->id]]);
				if (!$coupon_user && $this->request->getPost('users')[$user->id]) {
					$coupon_user = new CouponUser;
					$coupon_user->user_id = $user->id;
					$coupon_user->coupon  = $this->_coupon;
					$coupon_user->create();
				} else if ($coupon_user && !$this->request->getPost('users')[$user->id]) {
					$coupon_user->delete();
				}
			}
			$this->flashSession->success('Update member berhasil!');
		}
		$this->response->redirect('/admin/coupon_users/index/coupon_id:' . $this->_coupon->id . ($this->_current_page > 1 ? '/page:' . $this->_current_page : '') . ($this->_query_string ? '?' . $this->_query_string : ''));
	}

	function deleteAction($id) {
		if ($this->request->isPost()) {
			$coupon_user = CouponUser::findFirst([
				'coupon_id = ?0 AND user_id = ?1',
				'bind' => [$this->_coupon->id, $id]
			]);
			if ($coupon_user) {
				$coupon_user->delete();
			}
		}
	}

	private function _prepare_datas(&$offset) {
		$limit               = $this->config->per_page;
		$this->_current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset              = ($this->_current_page - 1) * $limit;
		$keyword             = $this->request->get('keyword', 'string');
		$this->_query_string = '';
		$roles               = new Map;
		foreach (Role::find(['conditions' => 'name IN ({names:array})', 'bind' => ['names' => ['Merchant', 'Buyer']], 'columns' => 'id, name']) as $role) {
			$roles->put($role->id, $role);
		}
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.mobile_phone',
				'a.status',
				'c.coupon_id',
				'role' => 'b.name',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\Role', 'a.role_id = b.id', 'b')
			->leftJoin('Application\Models\CouponUser', "a.id = c.user_id AND c.coupon_id = {$this->_coupon->id}", 'c')
			->orderBy('a.name ASC');
		$role_id = $this->request->get('role_id', 'int');
		if ($role_id && $roles->hasKey($role_id)) {
			$builder->where('a.role_id = :role_id:', ['role_id' => $role_id]);
			$this->_query_string = 'role_id=' . $role_id;
		} else {
			$builder->inWhere('a.role_id', [Role::MERCHANT, Role::BUYER]);
		}
		if ($keyword) {
			$builder->andWhere('a.name LIKE :keyword: OR a.mobile_phone LIKE :keyword:', ['keyword' => "%{$keyword}%"]);
			$this->_query_string .= ($this->_query_string ? '&' : '') . 'keyword=' . $keyword;
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $this->_current_page,
		]);
		$this->view->roles        = $roles->values()->toArray();
		$this->view->role_id      = $role_id;
		$this->view->keyword      = $keyword;
		$this->view->query_string = $this->_query_string;
		return $paginator->getPaginate();
	}
}
