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

	function menus($expanded) {
		return [
			'Dashboard'   => [
				'link' => 'home',
				'icon' => 'home',
			],
			'Mailbox'     => [
				'link'     => '#',
				'icon'     => 'envelope',
				'expanded' => $expanded == 'Mailbox',
				'items'    => [
					'Inbox'       => 'messages/unread',
					'Outbox'      => 'messages/read',
					'Kirim Pesan' => 'messages/new',
					'Semua Pesan' => 'message',
				],
			],
			'Content'     => [
				'link'     => '#',
				'icon'     => 'copy',
				'expanded' => $expanded == 'Content',
				'items'    => [
					'Pages'	       => 'pages',
					'Page Setting' => 'page_categories',
					'Content'      => 'contents',
					'Banner'       => 'banners',
					'File Manager' => 'files',
				],
			],
			'Members'     => [
				'link'     => '#',
				'icon'     => 'users',
				'expanded' => $expanded == 'Members',
				'items'    => [
					'Member List'                    => 'users',
					'Tambah Member'                  => 'users/new',
					'Export Email Member'            => 'users/export_email',
					'Invoice'                        => 'invoices',
					'Transaksi'                      => 'transactions',
					'Withdraw Member'                => 'withdrawals',
					'Tambah / Potong Dompet'         => 'deposits',
					'Tambah / Potong Reward Pembeli' => 'deposits',
					'Upgrade membership'             => 'users/upgrade_membership',
					'Member Testimonies'             => 'testimonies',
				],
			],
			'Products'    => [
				'link'     => '#',
				'icon'     => 'coffee',
				'expanded' => $expanded == 'Products',
				'items'    => [
					'Category Produk'       => 'product_categories',
					'Category Meta'         => 'product_category_metas',
					'Brand Produk'          => 'brands',
					'Brand Meta'            => 'product_metas',
					'Tambah Produk Baru'    => 'products/new',
					'Produk List'           => 'products',
					'Slot Category'         => 'slot_categories',
					'Slot Meta'             => 'slot_metas',
					'Slot List'             => 'slots',
					'Member Wishlist'       => 'wishlists',
					'Member Product Review' => 'product_reviews',
					'Product Meta'          => 'product_metas',
					'Category Poin'         => 'point_categories',
					'Poin'                  => 'points',
					'Poin Meta'             => 'point_metas',
					'Permintaan Poin'       => 'point_requests',
					'Poin Log'              => 'point_logs',
					'Kupon Member'          => 'coupon_users',
					'Discount'              => 'discounts',
				],
			],
			'Order'       => [
				'link'     => '#',
				'icon'     => 'shopping-cart',
				'expanded' => $expanded == 'Order',
				'items'    => [
					'Buat Order Baru'                  => 'orders/new',
					'Order List'                       => 'orders',
					'Data Propinsi / Kota / Kecamatan' => 'provinces',
					'Kota Asal Pengiriman'             => 'shipping_origins',
					'Pengiriman'                       => 'shippings',
					'Opsi Pengiriman'                  => 'shipping_options',
					'Email Template'                   => 'email_templates',
				],
			],
			'Options'     => [
				'link'     => '#',
				'icon'     => 'wrench',
				'expanded' => $expanded == 'Options',
				'items'    => [
					'Rekening Admin'  => 'bank_accounts',
					'User Setting'    => 'user_settings',
					'Admin Setting'   => 'admin_settings',
					'User Notifikasi' => 'user_notifications',
					'User Message'    => 'user_messages',
					'Keyword Log'     => 'searches',
					'Short Link'      => 'short_urls',
					'Random Text'     => 'random_texts',
				],
			],
			'Manage User' => 'users',
			'Logout'      => 'sessions/delete',
		];
	}
}