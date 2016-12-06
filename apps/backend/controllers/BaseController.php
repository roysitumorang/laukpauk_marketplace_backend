<?php

namespace Application\Backend\Controllers;

use Ds\Vector;
use Phalcon\Db;
use Phalcon\Mvc\Controller;
use Phalcon\Text;

class BaseController extends Controller {
	function initialize() {
		$url = $this->request->getQuery('_url');
		if (!$this->session->get('user_id') && !Text::startsWith($url, '/admin/sessions')) {
			return $this->response->redirect('/admin/sessions/create?next=' . $url);
		}
		$this->currentUser->update(['last_seen' => $this->currentDatetime->format('Y-m-d H:i:s')]);
		if (!apcu_exists('subdistricts')) {
			$subdistricts = [];
			$result       = $this->db->query("SELECT a.id, a.name FROM subdistricts a JOIN cities b ON a.city_id = b.id WHERE b.name = 'Medan' ORDER BY a.name");
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($subdistrict = $result->fetch()) {
				$subdistricts[] = $subdistrict;
			}
			apcu_add('subdistricts', $subdistricts);
		}
		if (!apcu_exists('villages')) {
			$villages = [];
			$result   = $this->db->query('SELECT id, subdistrict_id, name FROM villages ORDER BY subdistrict_id, name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				isset($villages[$item->subdistrict_id]) || $villages[$item->subdistrict_id] = [];
				$village = clone $item;
				unset($village->subdistrict_id);
				$villages[$item->subdistrict_id][] = $village;
			}
			apcu_add('villages', $villages);
		}
		$this->view->current_user         = $this->currentUser;
		$this->view->unread_notifications = $this->currentUser->getRelated('notifications', [
			'conditions' => 'read_at IS NULL',
			'columns'    => 'id, subject, link',
			'order'      => 'id DESC',
		])->toArray();
		$this->view->unread_messages      = $this->currentUser->getRelated('messages', [
			'conditions' => 'Application\Models\MessageRecipient.read_at IS NULL',
			'columns'    => 'Application\Models\Message.id, Application\Models\Message.subject, Application\Models\Message.body',
			'order'      => 'Application\Models\Message.id DESC',
		])->toArray();
	}

	function notFoundAction() {
		// Send a HTTP 404 response header
		$this->response->setStatusCode(404, 'Not Found');
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
				],
			], [
				'label'     => 'Content',
				'link'      => '#',
				'icon'      => 'copy',
				'expanded'  => $expanded == 'Content',
				'sub_items' => [
					['label' => 'Page',         'link' => 'page_categories'],
					['label' => 'Content',      'link' => 'post_categories'],
					['label' => 'Banner',       'link' => 'banner_categories'],
					['label' => 'File Manager', 'link' => 'files'],
				],
			], [
				'label'     => 'Members',
				'link'      => '#',
				'icon'      => 'users',
				'expanded'  => $expanded == 'Members',
				'sub_items' => [
					['label' => 'Member List',                    'link' => 'users'],
					['label' => 'Tambah Member',                  'link' => 'users/create'],
					['label' => 'Export Email Member',            'link' => 'users/export_email'],
					['label' => 'Invoice',                        'link' => 'invoices'],
					['label' => 'Transaksi',                      'link' => 'transactions'],
					['label' => 'Withdraw Member',                'link' => 'withdrawals'],
					['label' => 'Tambah / Potong Dompet',         'link' => 'deposits'],
					['label' => 'Tambah / Potong Reward Pembeli', 'link' => 'deposits'],
					['label' => 'Upgrade Membership',             'link' => 'users/upgrade_membership'],
					['label' => 'Member Testimonies',             'link' => 'testimonies'],
				],
			], [
				'label'     => 'Products',
				'link'      => '#',
				'icon'      => 'coffee',
				'expanded'  => $expanded == 'Products',
				'sub_items' => [
					['label' => 'Category Produk'      , 'link' => 'product_categories'],
					['label' => 'Category Meta'        , 'link' => 'product_category_metas'],
					['label' => 'Brand Produk'         , 'link' => 'brands'],
					['label' => 'Brand Meta'           , 'link' => 'product_metas'],
					['label' => 'Tambah Produk Baru'   , 'link' => 'products/create'],
					['label' => 'Produk List'          , 'link' => 'products'],
					['label' => 'Slot Category'        , 'link' => 'slot_categories'],
					['label' => 'Slot Meta'            , 'link' => 'slot_metas'],
					['label' => 'Slot List'            , 'link' => 'slots'],
					['label' => 'Member Wishlist'      , 'link' => 'wishlists'],
					['label' => 'Member Product Review', 'link' => 'product_reviews'],
					['label' => 'Product Meta'         , 'link' => 'product_metas'],
					['label' => 'Category Poin'        , 'link' => 'point_categories'],
					['label' => 'Poin'                 , 'link' => 'points'],
					['label' => 'Poin Meta'            , 'link' => 'point_metas'],
					['label' => 'Permintaan Poin'      , 'link' => 'point_requests'],
					['label' => 'Poin Log'             , 'link' => 'point_logs'],
					['label' => 'Kupon Member'         , 'link' => 'coupon_users'],
					['label' => 'Discount'             , 'link' => 'discounts'],
				],
			], [
				'label'     => 'Order',
				'link'      => '#',
				'icon'      => 'shopping-cart',
				'expanded'  => $expanded == 'Order',
				'sub_items' => [
					['label' => 'Buat Order Baru'                 , 'link' => 'orders/create'],
					['label' => 'Order List'                      , 'link' => 'orders'],
					['label' => 'Data Propinsi / Kota / Kecamatan', 'link' => 'provinces'],
					['label' => 'Kota Asal Pengiriman'            , 'link' => 'shipping_origins'],
					['label' => 'Pengiriman'                      , 'link' => 'shippings'],
					['label' => 'Opsi Pengiriman'                 , 'link' => 'shipping_options'],
					['label' => 'Email Template'                  , 'link' => 'email_templates'],
				],
			], [
				'label'     => 'Options',
				'link'      => '#',
				'icon'      => 'wrench',
				'expanded'  => $expanded == 'Options',
				'sub_items' => [
					['label' => 'Rekening Admin' , 'link' => 'bank_accounts'],
					['label' => 'User Setting'   , 'link' => 'user_settings'],
					['label' => 'Admin Setting'  , 'link' => 'admin_settings'],
					['label' => 'User Notifikasi', 'link' => 'user_notifications'],
					['label' => 'User Message'   , 'link' => 'user_messages'],
					['label' => 'Keyword Log'    , 'link' => 'searches'],
					['label' => 'Short Link'     , 'link' => 'short_urls'],
					['label' => 'Random Text'    , 'link' => 'random_texts'],
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