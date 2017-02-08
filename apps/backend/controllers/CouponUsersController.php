<?php

namespace Application\Backend\Controllers;

use Application\Models\Coupon;
use Application\Models\CouponUser;
use Phalcon\Paginator\Adapter\Model;
use Phalcon\Paginator\Adapter\QueryBuilder;

class CouponUsersController extends ControllerBase {
	private $_coupon;

	function initialize() {
		parent::initialize();
		if (!($coupon_id = $this->dispatcher->getParam('coupon_id')) || !($this->_coupon = Coupon::findFirstById($coupon_id))) {
			$this->flashSession->error('Kupon tidak ditemukan.');
			$this->response->redirect('coupons');
			return false;
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->request->get('keyword', 'string');
		$params       = [];
		if ($keyword) {
			$params = [
				'Application\Models\User.name LIKE :keyword: OR Application\Models\User.mobile_phone LIKE :keyword:',
				'bind' => ['keyword' => "%{$keyword}%"],
			];
		}
		$params['order'] = 'Application\Models\User.name';
		$paginator       = new Model([
			'data'  => $this->_coupon->getRelated('users', $params),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		$users = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$users[] = $item;
		}
		$this->view->menu    = $this->_menu('Products');
		$this->view->coupon  = $this->_coupon;
		$this->view->users   = $users;
		$this->view->page    = $page;
		$this->view->pages   = $pages;
		$this->view->keyword = $keyword;
	}

	function createAction() {
		$keyword      = $this->request->get('keyword', 'string');
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		if ($this->request->isPost() && ($user_id = $this->request->getPost('user_id', 'int')) && $this->db->fetchColumn("SELECT COUNT(1) FROM users a WHERE NOT EXISTS(SELECT 1 FROM coupon_users b WHERE b.coupon_id = {$this->_coupon->id} AND b.user_id = a.id) AND a.id = {$user_id} AND a.role_id = 4 AND a.status = 1")) {
			$coupon_user            = new CouponUser;
			$coupon_user->coupon_id = $this->_coupon->id;
			$coupon_user->user_id   = $user_id;
			if ($coupon_user->create()) {
				$this->flashSession->success('Penambahan member berhasil.');
				return $this->response->redirect('/admin/coupon_users/create/coupon_id:' . $this->_coupon->id . ($current_page > 1 ? "/page:{$current_page}" : '') . ($keyword ? "?keyword={$keyword}" : ''));
			}
		}
		$limit   = $this->config->per_page;
		$offset  = ($current_page - 1) * $limit;
		$builder = $this->modelsManager->createBuilder()
			->columns(['a.id', 'a.name', 'a.mobile_phone'])
			->from(['a' => 'Application\Models\User'])
			->where('NOT EXISTS(SELECT 1 FROM Application\Models\CouponUser b WHERE b.coupon_id = :coupon_id: AND b.user_id = a.id)', ['coupon_id' => $this->_coupon->id])
			->andWhere('a.role_id = 4')
			->andWhere('a.status = 1')
			->orderBy('a.name');
		if ($keyword) {
			$builder->andWhere('(a.name LIKE :keyword: OR a.mobile_phone LIKE :keyword:)', ['keyword' => "%{$keyword}%"]);
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		$users = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$users[] = $item;
		}
		$this->view->menu    = $this->_menu('Products');
		$this->view->coupon  = $this->_coupon;
		$this->view->users   = $users;
		$this->view->page    = $page;
		$this->view->pages   = $pages;
		$this->view->keyword = $keyword;
	}

	function deleteAction($id) {
		$coupon_user = CouponUser::findFirst([
			'coupon_id = ?0 AND user_id = ?1',
			'bind' => [$this->_coupon->id, $id]
		]);
		if (!$coupon_user) {
			$this->flashSession->error('Member tidak ditemukan.');
		} else {
			$coupon_user->delete();
			$this->flashSession->success('Member berhasil dihapus.');
		}
		return $this->response->redirect('/admin/coupon_users/index/coupon_id:' . $this->_coupon->id);
	}
}
