<?php

namespace Application\Backend\Controllers;

use Ds\Vector;
use Phalcon\Db;
use Phalcon\Mvc\Controller;
use Phalcon\Text;

class ControllerBase extends Controller {
	function initialize() {
		if (!$this->currentUser) {
			$url = $this->request->getQuery('_url');
			if (!Text::startsWith($url, '/admin/sessions/create')) {
				$this->response->redirect('/admin/sessions/create?next=' . $url);
			}
			return;
		}
		$this->currentUser->update(['last_seen' => $this->currentDatetime->format('Y-m-d H:i:s')]);
		if (!apcu_exists('subdistricts')) {
			$subdistricts = new Vector;
			$result       = $this->db->query("SELECT a.id, a.name FROM subdistricts a JOIN cities b ON a.city_id = b.id WHERE b.name = 'Medan' ORDER BY a.name");
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($subdistrict = $result->fetch()) {
				$subdistricts->push($subdistrict);
			}
			apcu_add('subdistricts', $subdistricts->toArray());
		}
		if (!apcu_exists('villages')) {
			$villages = [];
			$result   = $this->db->query('SELECT id, subdistrict_id, name FROM villages ORDER BY subdistrict_id, name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				isset($villages[$item->subdistrict_id]) || $villages[$item->subdistrict_id] = [];
				$villages[$item->subdistrict_id][] = (object)[
					'id'   => $item->id,
					'name' => $item->name,
				];
			}
			apcu_add('villages', $villages);
		}
		$this->view->current_user         = $this->currentUser;
		$this->view->unread_notifications = $this->currentUser->getRelated('notifications', [
			'conditions' => 'Application\Models\NotificationRecipient.read_at IS NULL',
			'columns'    => 'Application\Models\Notification.id, Application\Models\Notification.subject, Application\Models\Notification.link, Application\Models\Notification.created_at',
			'order'      => 'Application\Models\Notification.id DESC',
		]);
		$this->view->unread_messages      = $this->currentUser->getRelated('messages', [
			'conditions' => 'Application\Models\MessageRecipient.read_at IS NULL',
			'columns'    => 'Application\Models\Message.id, Application\Models\Message.subject, Application\Models\Message.body',
			'order'      => 'Application\Models\Message.id DESC',
		]);
	}

	protected function _menu(string $expanded = null) : array {
		return [[
				'label'     => 'Dashboard',
				'link'      => 'home',
				'icon'      => 'home',
			], [
				'label'     => 'Mailbox',
				'link'      => '#',
				'icon'      => 'envelope',
				'expanded'  => $expanded == 'Mailbox',
				'sub_items' => [
					['label' => 'Inbox',       'link' => 'messages/index/unread:1'],
					['label' => 'Outbox',      'link' => 'messages/index/read:1'],
					['label' => 'Kirim Pesan', 'link' => 'messages/create'],
					['label' => 'Semua Pesan', 'link' => 'messages'],
					['label' => 'Feedbacks',   'link' => 'feedbacks'],
				],
			], [
				'label'     => 'Content',
				'link'      => '#',
				'icon'      => 'copy',
				'expanded'  => $expanded == 'Content',
				'sub_items' => [
					['label' => 'Page',    'link' => 'page_categories'],
					['label' => 'Content', 'link' => 'post_categories'],
					['label' => 'Banner',  'link' => 'banner_categories'],
				],
			], [
				'label'     => 'Members',
				'link'      => '#',
				'icon'      => 'users',
				'expanded'  => $expanded == 'Members',
				'sub_items' => [
					['label' => 'Member List',   'link' => 'users'],
					['label' => 'Tambah Member', 'link' => 'users/create'],
				],
			], [
				'label'     => 'Products',
				'link'      => '#',
				'icon'      => 'coffee',
				'expanded'  => $expanded == 'Products',
				'sub_items' => [
					['label' => 'Category Produk', 'link' => 'product_categories'],
					['label' => 'Tambah Produk',   'link' => 'products/create'],
					['label' => 'Produk List'    , 'link' => 'products'],
					['label' => 'Kupon Member'   , 'link' => 'coupons'],
				],
			], [
				'label'     => 'Order',
				'link'      => '#',
				'icon'      => 'shopping-cart',
				'expanded'  => $expanded == 'Order',
				'sub_items' => [
					['label' => 'Order List', 'link' => 'orders'],
				],
			], [
				'label'     => 'Manage User',
				'link'      => 'users',
				'icon'      => 'user-plus',
			], [
				'label'     => 'Logout',
				'link'      => 'sessions/delete',
				'icon'      => 'sign-out',
			],
		];
	}

	protected function _setPaginationRange($page) : array {
		$paging_limit   = 10;
		$paging_total   = ceil($page->last / $paging_limit);
		$paging_current = ceil($page->current / $paging_limit);
		$start          = 1;
		$end            = $page->last;
		if ($paging_current <= $paging_total) {
			$offset = ($paging_current - 1) * $paging_limit;
			$start  = $offset + 1;
			$end    = min($offset + $paging_limit, $page->last);
		}
		$pages = new Vector(range($start, $end));
		if ($start > 2) {
			$pages->insert(0, $start - 1);
		}
		if ($start > 1) {
			$pages->insert(0, 1);
		}
		$next = $end + 1;
		if ($next < $page->last) {
			$pages->push($next);
		}
		if ($end < $page->last) {
			$pages->push($page->last);
		}
		return $pages->toArray();
	}
}