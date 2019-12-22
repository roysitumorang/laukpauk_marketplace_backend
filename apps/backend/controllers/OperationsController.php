<?php

namespace Application\Backend\Controllers;

use Application\Models\{Role, User};
use Phalcon\Paginator\Adapter\Model;

class OperationsController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int!', 1);
		$offset         = ($current_page - 1) * $limit;
		$search_query   = $this->dispatcher->getParam('keyword', 'string');
		$business_hours = [];
		$merchants      = [];
		$conditions     = [
			'status = 1 AND role_id = ?0',
			'bind'  => [Role::MERCHANT],
			'order' => 'company',
		];
		if ($search_query) {
			$keywords = preg_split('/\s/', $search_query, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($keywords as $i => $keyword) {
				$conditions[0]       .= ' AND company ILIKE ?' . ($i + 1);
				$conditions['bind'][] = "%{$keyword}%";
			}
		}
		$paginator = new Model([
			'data'  => User::find($conditions),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page  = $paginator->paginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$merchants[] = $item;
		}
		foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
			$business_hours[$hour] = ($hour < 10 ? '0' . $hour : $hour) . ':00';
		}
		$this->view->setVars([
			'menu'           => $this->_menu('Products'),
			'page'           => $page,
			'pages'          => $pages,
			'keyword'        => $search_query,
			'merchants'      => $merchants,
			'business_hours' => $business_hours,
		]);
	}

	function updateAction() {
		if ($this->request->isPost() && ($merchants = $this->request->getPost('merchants'))) {
			$users = User::find(['status = 1 AND role_id = ?0 AND id IN({ids:array})', 'bind' => [Role::MERCHANT, 'ids' => array_keys($merchants)], 'limit' => $this->config->per_page]);
			foreach ($users as $user) {
				$operation = $merchants[$user->id];
				$user->assign($operation, null, [
					'business_opening_hour',
					'business_closing_hour',
					'delivery_hours',
					'minimum_purchase',
					'address',
				]);
				$user->setOpenOnSunday($operation['open_on_sunday']);
				$user->setOpenOnMonday($operation['open_on_monday']);
				$user->setOpenOnTuesday($operation['open_on_tuesday']);
				$user->setOpenOnWednesday($operation['open_on_wednesday']);
				$user->setOpenOnThursday($operation['open_on_thursday']);
				$user->setOpenOnFriday($operation['open_on_friday']);
				$user->setOpenOnSaturday($operation['open_on_saturday']);
				$user->update();
			}
			$this->flashSession->success('Update hari jam operasional berhasil.');
		}
		$this->dispatcher->forward(['action' => 'index']);
	}

	function updateAllAction() {
		if ($this->request->isPost()) {
			$this->db->begin();
			if ($this->db->execute('UPDATE users SET open_on_sunday = ?, open_on_monday = ?, open_on_tuesday = ?, open_on_wednesday = ?, open_on_thursday = ?, open_on_friday = ?, open_on_saturday = ? WHERE status = 1 AND role_id = ?', [
				$this->request->getPost('open_on_sunday', 'int!') ? 1 : 0,
				$this->request->getPost('open_on_monday', 'int!') ? 1 : 0,
				$this->request->getPost('open_on_tuesday', 'int!') ? 1 : 0,
				$this->request->getPost('open_on_wednesday', 'int!') ? 1 : 0,
				$this->request->getPost('open_on_thursday', 'int!') ? 1 : 0,
				$this->request->getPost('open_on_friday', 'int!') ? 1 : 0,
				$this->request->getPost('open_on_saturday', 'int!') ? 1 : 0,
				Role::MERCHANT,
			])) {
				$this->flashSession->success('Update hari operasional untuk semua Merchant berhasil.');
				$this->db->commit();
			} else {
				$this->flashSession->error('Update hari operasional untuk semua Merchant tidak berhasil.');
				$this->db->rollback;
			}
		}
		$this->dispatcher->forward(['action' => 'index']);
	}
}