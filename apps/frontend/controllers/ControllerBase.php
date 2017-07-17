<?php

namespace Application\Frontend\Controllers;

use DateTime;
use Ds\Vector;
use IntlDateFormatter;
use Phalcon\Mvc\Controller;
use Phalcon\Text;

class ControllerBase extends Controller {
	function beforeExecuteRoute() {
		register_shutdown_function(function() {
			$this->db->insertAsDict('api_calls', [
				'user_id'        => $this->currentUser->id,
				'url'            => $this->request->getServer('REQUEST_URI'),
				'request_method' => $this->request->getMethod(),
				'ip_address'     => $this->request->getServer('REMOTE_ADDR'),
				'user_agent'     => $this->request->getServer('HTTP_USER_AGENT'),
				'execution_time' => (microtime(true) - $this->request->getServer('REQUEST_TIME_FLOAT')) * 1000,
				'memory_usage'   => memory_get_peak_usage(true) / 1048576,
				'created_at'     => $this->currentDatetime->format('Y-m-d H:i:s'),
			]);
		});
		if (!$this->currentUser) {
			$url = $this->request->getQuery('_url');
			if (!Text::startsWith($url, '/sessions/create')) {
				$this->response->redirect('/sessions/create' . ($url && $url != '/home' ? "?next={$url}" : ''));
				$this->response->send();
			}
			return;
		}
		$unread_notifications = [];
		$datetime_formatter   = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'd MMM yyyy HH.mm'
		);
		$result = $this->currentUser->getRelated('notifications', [
			'conditions' => 'Application\Models\NotificationRecipient.read_at IS NULL',
			'columns'    => 'Application\Models\Notification.id, Application\Models\Notification.title, Application\Models\Notification.merchant_target_url, Application\Models\Notification.created_at',
			'order'      => 'Application\Models\Notification.id DESC',
		]);
		foreach ($result as $item) {
			$item->writeAttribute('created_at', $datetime_formatter->format(new DateTime($item->created_at, $this->currentDatetime->getTimezone())));
			$unread_notifications[] = $item;
		}
		$this->view->current_user         = $this->currentUser;
		$this->view->unread_notifications = $unread_notifications;
		$this->view->unread_messages      = $this->currentUser->getRelated('messages', [
			'conditions' => 'Application\Models\MessageRecipient.read_at IS NULL',
			'columns'    => 'Application\Models\Message.id, Application\Models\Message.subject, Application\Models\Message.body',
			'order'      => 'Application\Models\Message.id DESC',
		]);
		$this->currentUser->update(['last_seen' => $this->currentDatetime->format('Y-m-d H:i:s')]);
	}

	protected function _menu(string $expanded = null) : array {
		return [[
				'label'     => 'Dashboard',
				'link'      => 'home',
				'icon'      => 'home',
			], [
				'label'     => 'Mailbox',
				'link'      => null,
				'icon'      => 'envelope',
				'expanded'  => $expanded == 'Mailbox',
				'sub_items' => [
					['label' => 'Feedbacks',         'link' => 'feedbacks'],
					['label' => 'Notifikasi Mobile', 'link' => 'mobile_notifications'],
				],
			], [
				'label'     => 'Content',
				'link'      => null,
				'icon'      => 'copy',
				'expanded'  => $expanded == 'Content',
				'sub_items' => [
					['label' => 'Page',    'link' => 'page_categories'],
					['label' => 'Content', 'link' => 'post_categories'],
					['label' => 'Banner',  'link' => 'banner_categories'],
				],
			], [
				'label'     => 'Members',
				'link'      => null,
				'icon'      => 'users',
				'expanded'  => $expanded == 'Members',
				'sub_items' => [
					['label' => 'Member List',   'link' => 'users'],
					['label' => 'Tambah Member', 'link' => 'users/create'],
				],
			], [
				'label'     => 'Products',
				'link'      => null,
				'icon'      => 'coffee',
				'expanded'  => $expanded == 'Products',
				'sub_items' => [
					['label' => 'Daftar Kategori Produk', 'link' => 'product_categories'],
					['label' => 'Tambah Produk',          'link' => 'products/create'],
					['label' => 'Daftar Produk',          'link' => 'products'],
					['label' => 'Group Produk',           'link' => 'product_groups'],
					['label' => 'Kupon Member',           'link' => 'coupons'],
					['label' => 'Pembayaran Deposit',     'link' => 'payments'],
				],
			], [
				'label'     => 'Order',
				'link'      => null,
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