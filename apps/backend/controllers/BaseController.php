<?php

namespace Application\Backend\Controllers;
use Phalcon\Mvc\Controller;
use Phalcon\Text;

class BaseController extends Controller {
	function initialize() {
		$url = $this->request->getQuery('_url');
		if ($this->session->get('user_id')) {
			$this->view->current_user = $this->currentUser;
			$this->view->unread_messages = $this->currentUser->unread_messages;
		} else if (!Text::startsWith($url, '/admin/sessions')) {
			$this->response->redirect('/admin/sessions/new');
		}
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
					['label' => 'Inbox',       'link' => 'messages/unread'],
					['label' => 'Outbox',      'link' => 'messages/read'],
					['label' => 'Kirim Pesan', 'link' => 'messages/new'],
					['label' => 'Semua Pesan', 'link' => 'message'],
				],
			], [
				'label'     => 'Content',
				'link'      => '#',
				'icon'      => 'copy',
				'expanded'  => $expanded == 'Content',
				'sub_items' => [
					['label' => 'Pages',        'link' => 'pages'],
					['label' => 'Page Setting', 'link' => 'page_categories'],
					['label' => 'Content',      'link' => 'contents'],
					['label' => 'Banner',       'link' => 'banners'],
					['label' => 'File Manager', 'link' => 'files'],
				],
			], [
				'label'     => 'Members',
				'link'      => '#',
				'icon'      => 'users',
				'expanded'  => $expanded == 'Members',
				'sub_items' => [
					['label' => 'Member List',                    'link' => 'users'],
					['label' => 'Tambah Member',                  'link' => 'users/new'],
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
					['label' => 'Tambah Produk Baru'   , 'link' => 'products/new'],
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
					['label' => 'Buat Order Baru'                 , 'link' => 'orders/new'],
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
}