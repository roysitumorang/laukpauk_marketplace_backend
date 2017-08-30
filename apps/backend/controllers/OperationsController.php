<?php

namespace Application\Backend\Controllers;

use Application\Models\Role;
use Application\Models\User;
use Phalcon\Paginator\Adapter\Model;

class OperationsController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$search_query   = $this->dispatcher->getParam('keyword', 'string');
		$business_hours = [];
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
		$page      = $paginator->getPaginate();
		$pages     = $this->_setPaginationRange($page);
		$merchants = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$merchants[] = $item;
		}
		foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
			$business_hours[$hour] = ($hour < 10 ? '0' . $hour : $hour) . ':00';
		}
		$this->view->menu           = $this->_menu('Products');
		$this->view->page           = $page;
		$this->view->pages          = $pages;
		$this->view->keyword        = $search_query;
		$this->view->merchants      = $merchants;
		$this->view->business_hours = $business_hours;
	}

	function updateAction() {
		$merchants = $this->request->getPost('merchants');
		if ($this->request->isPost()) {
			foreach ($merchants as $id => $operation) {
				if ($user = User::findFirst(['status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [Role::MERCHANT, $id]])) {
					$user->setOpenOnSunday($operation['open_on_sunday']);
					$user->setOpenOnMonday($operation['open_on_monday']);
					$user->setOpenOnTuesday($operation['open_on_tuesday']);
					$user->setOpenOnWednesday($operation['open_on_wednesday']);
					$user->setOpenOnThursday($operation['open_on_thursday']);
					$user->setOpenOnFriday($operation['open_on_friday']);
					$user->setOpenOnSaturday($operation['open_on_saturday']);
					$user->setBusinessOpeningHour($operation['business_opening_hour']);
					$user->setBusinessClosingHour($operation['business_closing_hour']);
					$user->setDeliveryHours($operation['delivery_hours']);
					$user->setMinimumPurchase($operation['minimum_purchase']);
					$user->setAddress($operation['address']);
					$user->update();
				}
			}
			$this->flashSession->success('Update hari jam operasional berhasil.');
		}
		$this->dispatcher->forward(['action' => 'index']);
	}
}