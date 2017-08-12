<?php

namespace Application\Backend\Controllers;

use Application\Models\Order;
use Application\Models\OrderProduct;
use Application\Models\Role;
use Application\Models\Setting;
use Application\Models\User;
use Application\Models\Village;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Ds\Map;
use Exception;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class OrdersController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Order');
	}

	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$parameters     = [];
		$conditions     = [];
		$status         = Order::STATUS;
		$current_status = filter_var($this->dispatcher->getParam('status'), FILTER_VALIDATE_INT);
		if ($date = $this->dispatcher->getParam('from')) {
			try {
				$from         = (new DateTimeImmutable($date))->format('Y-m-d');
				$conditions[] = "DATE(a.created_at) >= '{$from}'";
			} catch (Exception $e) {
				unset($from);
			}
		}
		if ($date = $this->dispatcher->getParam('to')) {
			try {
				$to           = (new DateTimeImmutable($date))->format('Y-m-d');
				$conditions[] = "DATE(a.created_at) <= '{$to}'";
			} catch (Exception $e) {
				unset($to);
			}
		}
		if ($code = $this->dispatcher->getParam('code', 'int')) {
			$conditions[] = "a.code = '{$code}'";
		}
		if (array_key_exists($current_status, $status)) {
			$conditions[] = "a.status = {$current_status}";
		}
		if ($conditions) {
			$parameters[] = implode(' AND ', $conditions);
		}
		if ($mobile_phone = $this->dispatcher->getParam('mobile_phone')) {
			$conditions[] = "b.mobile_phone = '{$mobile_phone}'";
		}
		$parameters['order'] = 'a.id DESC';
		$builder             = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.code',
				'a.name',
				'a.email',
				'a.address',
				'a.village_id',
				'buyer_phone'      => 'a.mobile_phone',
				'merchant_name'    => 'b.name',
				'merchant_company' => 'b.company',
				'merchant_phone'   => 'b.mobile_phone',
				'a.status',
				'a.final_bill',
				'a.merchant_id',
				'a.buyer_id',
				'a.admin_fee',
				'a.original_bill',
				'a.ip_address',
				'a.coupon_id',
				'a.scheduled_delivery',
				'a.actual_delivery',
				'a.note',
				'a.created_at',
			])
			->addFrom('Application\Models\Order', 'a')
			->join('Application\Models\User', 'a.merchant_id = b.id', 'b');
		if ($conditions) {
			$builder->where(implode(' AND ', $conditions));
		}
		$builder->orderBy($parameters['order']);
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page   = $paginator->getPaginate();
		$pages  = $this->_setPaginationRange($page);
		$orders = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$orders[] = $item;
		}
		$this->view->orders                 = $orders;
		$this->view->pages                  = $pages;
		$this->view->page                   = $paginator->getPaginate();
		$this->view->from                   = $from;
		$this->view->to                     = $to;
		$this->view->status                 = $status;
		$this->view->current_status         = $current_status;
		$this->view->code                   = $code;
		$this->view->mobile_phone           = $mobile_phone;
		$this->view->total_final_bill       = $this->db->fetchColumn('SELECT SUM(a.final_bill) FROM orders a' . ($parameters[0] ? " WHERE {$parameters[0]}" : '')) ?? 0;
		$this->view->total_admin_fee        = $this->db->fetchColumn('SELECT SUM(a.admin_fee) FROM orders a' . ($parameters[0] ? " WHERE {$parameters[0]}" : '')) ?? 0;
		$this->view->total_orders           = $this->db->fetchColumn('SELECT COUNT(1) FROM orders');
		$this->view->pending_orders         = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE status = 0");
		$this->view->completed_orders       = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE status = 1");
		$this->view->total_cancelled_orders = $this->db->fetchColumn("SELECT COUNT(1) FROM orders WHERE status = -1");
	}

	function showAction($id) {
		if (!$order = Order::findFirst(['code = ?0 OR id = ?1', 'bind' => [$id, $id]])) {
			$this->flashSession->error('Order tidak ditemukan.');
			return $this->dispatcher->forward('orders');
		}
		$order->writeAttribute('status', Order::STATUS[$order->status]);
		$this->view->order   = $order;
		$this->view->village = Village::findFirst($order->village_id);
	}

	function completeAction($id) {
		if (!$this->request->isPost() || !($order = Order::findFirst(['status = 0 AND (code = ?0 OR id = ?1)', 'bind' => [$id, $id]]))) {
			$this->flashSession->error('Order tidak ditemukan.');
			return $this->dispatcher->forward('orders');
		}
		$order->complete();
		$this->flashSession->success('Order #' . $order->code . ' telah selesai');
		return $this->response->redirect("/admin/orders/{$order->id}");
	}

	function cancelAction($id) {
		if (!$this->request->isPost() || !($order = Order::findFirst(['status = 0 AND (code = ?0 OR id = ?1)', 'bind' => [$id, $id]]))) {
			$this->flashSession->error('Order tidak ditemukan.');
			return $this->dispatcher->forward('orders');
		}
		if ($order->cancel($this->request->getPost('cancellation_reason'))) {
			$this->flashSession->success('Order #' . $order->code . ' telah dicancel');
		}
		foreach ($order->getMessages() as $error) {
			$this->flashSession->error($error);
		}
		return $this->response->redirect("/admin/orders/{$order->id}");
	}

	function printAction($id) {
		if (!$this->request->isPost() || !($order = Order::findFirst(['status = 1 AND (code = ?0 OR id = ?1)', 'bind' => [$id, $id]]))) {
			$this->flashSession->error('Order tidak ditemukan.');
			return $this->dispatcher->forward('orders');
		}
		$this->view->order = $order;
	}

	function createAction() {
		if (!($buyer_id = $this->dispatcher->getParam('buyer_id', 'int')) || !($buyer = User::findFirst(['status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [Role::BUYER, $buyer_id]]))) {
			$this->flashSession->error('Pembeli tidak ditemukan.');
			return $this->response->redirect('/admin/orders');
		}
		if ($this->session->has('order')) {
			$new_order = unserialize($this->session->get('order'));
			if ($new_order->hasKey('buyer_id') && $new_order->get('buyer_id') != $buyer->id) {
				$new_order->get('cart')->clear();
			}
		} else {
			$new_order = new Map;
			$new_order->put('buyer_id', $buyer->id);
			$new_order->put('cart', new Map);
		}
		if ($this->request->isPost()) {
			try {
				if ($new_order->get('cart')->isEmpty()) {
					throw new Exception('Keranjang belanja masih kosong!');
				}
				$orders           = [];
				$total            = 0;
				$coupon           = null;
				$discount         = 0;
				$minimum_purchase = Setting::findFirstByName('minimum_purchase')->value;
				try {
					$scheduled_delivery = new DateTime($new_order->get('scheduled_delivery'), $this->currentDatetime->getTimezone());
				} catch (Exception $ex) {
					throw new Exception('Tanggal/jam pengantaran tidak valid!');
				}
				if ($new_order->hasKey('coupon_id')) {
					$params = [<<<QUERY
						SELECT
							a.id,
							a.code,
							a.price_discount,
							a.discount_type,
							a.multiple_use,
							a.minimum_purchase,
							d.version AS minimum_version,
							a.maximum_usage,
							COUNT(DISTINCT b.id) AS personal_usage,
							COUNT(DISTINCT c.id) AS total_usage
						FROM
							coupons a
							LEFT JOIN orders b ON a.id = b.coupon_id AND b.status != '-1' AND b.buyer_id = ?
							LEFT JOIN orders c ON a.id = c.coupon_id AND c.status != '-1'
							LEFT JOIN releases d ON a.release_id = d.id
						WHERE
							a.status = '1' AND
							a.effective_date <= ? AND
							a.expiry_date > ? AND
							a.id = ? AND
							a.user_id
QUERY
						,
						$buyer->id,
						$this->currentDatetime->format('Y-m-d'),
						$this->currentDatetime->format('Y-m-d'),
						$new_order->get('coupon_id'),
					];
					$params[0] .= ($buyer->merchant_id ? " = {$buyer->merchant_id}" : ' IS NULL') . ' GROUP BY a.id, d.version';
					$coupon     = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
					if (!$coupon) {
						throw new Exception('Voucher tidak valid!');
					} else if ($coupon->maximum_usage && $coupon->total_usage >= $coupon->maximum_usage) {
						throw new Exception('Pemakaian voucher udah melebihi batas maksimal!');
					} else if (!$coupon->multiple_use && $coupon->personal_usage >= 1) {
						throw new Exception('Voucher cuma berlaku untuk sekali pemakaian!');
					}
				}
				foreach ($new_order->get('cart') as $merchant_id => $items) {
					$params = [<<<QUERY
						SELECT
							a.id,
							a.company,
							b.shipping_cost,
							a.minimum_purchase
						FROM
							users a
							JOIN coverage_area b ON a.id = b.user_id
						WHERE
							a.status = 1 AND
							a.role_id = ? AND
							a.id = ? AND
							b.village_id = ? AND
							a.premium_merchant
QUERY
						, Role::MERCHANT, $merchant_id, $buyer->village_id];
					$params[0] .= $buyer->merchant_id ? ' = 1' : ' IS NULL';
					$merchant   = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
					if (!$merchant) {
						throw new Exception('Merchant tidak valid!');
					}
					$order                     = new Order;
					$order_products            = [];
					$order->merchant_id        = $merchant->id;
					$order->name               = $buyer->name;
					$order->mobile_phone       = $buyer->mobile_phone;
					$order->address            = $this->request->getPost('address');
					$order->village_id         = $buyer->village_id;
					$order->original_bill      = 0;
					$order->scheduled_delivery = $scheduled_delivery->format('Y-m-d H:i:s');
					$order->note               = null;
					$order->buyer_id           = $buyer->id;
					$order->created_by         = $this->currentUser->id;
					$order->setTransaction($this->transactionManager->get());
					foreach ($items as $user_product_id => $quantity) {
						$product = $this->db->fetchOne('SELECT b.id, b.name, b.stock_unit, a.price, a.stock FROM user_product a JOIN products b ON a.product_id = b.id WHERE a.published = 1 AND b.published = 1 AND a.price > 0 AND a.stock > 0 AND a.user_id = ? AND a.id = ?', Db::FETCH_OBJ, [$merchant->id, $user_product_id]);
						if (!$product) {
							throw new Exception('Produk tidak valid!');
						}
						$order_product             = new OrderProduct;
						$order_product->product_id = $product->id;
						$order_product->name       = $product->name;
						$order_product->price      = $product->price;
						$order_product->stock_unit = $product->stock_unit;
						$order_product->quantity   = min(max($quantity, 0), $product->stock);
						$order_product->created_by = $this->currentUser->id;
						$order->original_bill     += $order_product->quantity * $product->price;
						$order_products[]          = $order_product;
					}
					if ($order->original_bill < $merchant->minimum_purchase) {
						throw new Exception('Belanja di ' . $merchant->company . ' minimal Rp. ' . number_format($merchant->minimum_purchase) . ' untuk dapat diproses!');
					}
					$order->final_bill    = $order->original_bill;
					$order->discount      = 0;
					$order->shipping_cost = $merchant->shipping_cost;
					$order->orderProducts = $order_products;
					if (!$order->validation()) {
						foreach ($order->getMessages() as $error) {
							$this->flashSession->error($error);
						}
						throw new Exception('Order tidak valid!');
					}
					$total   += $order->final_bill;
					$orders[] = $order;
				}
				if (!$buyer->merchant_id && $total < $minimum_purchase) {
					throw new Exception('Belanja minimal Rp. ' . number_format($minimum_purchase) . ' untuk dapat diproses!');
				}
				if ($coupon) {
					if ($total < $coupon->minimum_purchase) {
						throw new Exception('Voucher ' . $coupon->code . ' berlaku untuk belanja minimal Rp. ' . number_format($coupon->minimum_purchase) . '!');
					}
					$discount = $coupon->discount_type == 1 ? $coupon->price_discount : ceil($coupon->price_discount * $total / 100);
				}
				foreach ($orders as $order) {
					if ($discount) {
						$order->coupon_id = $coupon->id;
					}
					$order->discount   = min($order->final_bill, $discount);
					$order->final_bill = $order->final_bill - $order->discount + $order->shipping_cost;
					$discount          = max($discount - $order->discount, 0);
					$order->create();
				}
				$params = [];
				if ($buyer->name != $this->request->getPost('name')) {
					$params['name'] = $this->request->getPost('name');
				}
				if (!$buyer->address || $buyer->address != $this->request->getPost('address')) {
					$params['address'] = $this->request->getPost('address');
				}
				if ($params) {
					$buyer->update($params);
				}
				$this->flashSession->success('Order berhasil ditambah!');
				return $this->response->redirect('/admin/orders');
			} catch (Exception $e) {
				$this->flashSession->error($e->getMessage());
			}
		}
		$order                = new Order;
		$order->original_bill = 0;
		$order->shipping_cost = 0;
		$order->discount      = 0;
		$order->final_bill    = 0;
		$order_products       = [];
		$delivery_datetimes   = [];
		$coupons              = [];
		$product_categories   = [];
		$product_category_id  = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword              = $this->dispatcher->getParam('keyword');
		$current_page         = $this->dispatcher->getParam('page', 'int') ?: 1;
		$limit                = 15;
		$products             = [];
		$result               = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id WHERE a.user_id ' . ($buyer->merchant_id ? "= {$buyer->merchant_id}" : 'IS NULL') . ' GROUP BY a.id ORDER BY a.name');
		$builder             = $this->modelsManager->createBuilder()
			->columns([
				'b.id',
				'a.name',
				'a.stock_unit',
				'a.picture',
				'a.thumbnails',
				'b.price',
				'b.stock',
				'c.company',
				'c.mobile_phone',
				'c.address',
			])
			->from(['a' => 'Application\Models\Product'])
			->join('Application\Models\UserProduct', 'a.id = b.product_id', 'b')
			->join('Application\Models\User', 'b.user_id = c.id', 'c')
			->join('Application\Models\CoverageArea', 'c.id = d.user_id', 'd')
			->where("d.village_id = {$buyer->village_id}")
			->andWhere('a.published = 1')
			->andWhere('b.published = 1')
			->andWhere('b.price > 0')
			->andWhere('b.stock > 0')
			->andWhere('c.status = 1')
			->orderBy('a.name ASC, a.stock_unit ASC');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$product_categories[] = $row;
		}
		if ($buyer->merchant_id) {
			$builder->andWhere("b.user_id = {$buyer->merchant_id}");
		}
		if ($product_category_id) {
			$builder->andWhere("a.product_category_id = {$product_category_id}");
		}
		if ($keyword) {
			$builder->andWhere("a.name ILIKE '%{$keyword}%'");
		}
		if (!$new_order->get('cart')->isEmpty()) {
			$shipping_costs   = [];
			$current_datetime = new DateTime($this->currentDatetime->format('c'));
			$current_hour     = $current_datetime->format('G');
			$minimum_hour     = $current_hour + ($current_datetime->format('i') > 29 ? 2 : 1);
			$periods          = new DatePeriod($current_datetime, new DateInterval('P1D'), 1);
			foreach ($new_order->get('cart') as $merchant_id => $items) {
				foreach ($items as $user_product_id => $quantity) {
					$product = $this->db->fetchOne(<<<QUERY
						SELECT
							b.id,
							a.name,
							a.stock_unit,
							b.price,
							b.stock,
							c.company,
							c.mobile_phone,
							c.address,
							d.shipping_cost
						FROM
							products a
							JOIN user_product b ON a.id = b.product_id
							JOIN users c ON b.user_id = c.id
							JOIN coverage_area d ON c.id = d.user_id
						WHERE
							d.village_id = {$buyer->village_id}
							AND a.published = 1
							AND b.published = 1
							AND b.price > 0
							AND b.stock > 0
							AND c.status = 1
							AND b.user_id = {$merchant_id}
							AND b.id = {$user_product_id}
QUERY
						, Db::FETCH_OBJ
					);
					$product->quantity     = min($quantity, $product->stock);
					$order->original_bill += $product->price * $product->quantity;
					$order_products[]      = $product;
					if (!isset($shipping_costs[$merchant_id])) {
						$shipping_costs[$merchant_id] = $product->shipping_cost;
					}
				}
				$builder->andWhere('b.id NOT IN(' . $items->keys()->join(',') . ')');
			}
			$order->shipping_cost += array_sum(array_values($shipping_costs));
			$order->final_bill     = $order->original_bill + $order->shipping_cost;
			if ($new_order->hasKey('coupon_id')) {
				$params = [<<<QUERY
					SELECT
						a.id,
						a.code,
						a.price_discount,
						a.discount_type,
						a.multiple_use,
						a.minimum_purchase,
						a.maximum_usage,
						COUNT(DISTINCT b.id) AS personal_usage,
						COUNT(DISTINCT c.id) AS total_usage
					FROM
						coupons a
						LEFT JOIN orders b ON a.id = b.coupon_id AND b.status != '-1' AND b.buyer_id = ?
						LEFT JOIN orders c ON a.id = c.coupon_id AND c.status != '-1'
					WHERE
						a.status = '1' AND
						a.effective_date <= ? AND
						a.expiry_date > ? AND
						a.id = ? AND
						a.user_id
QUERY
					,
					$buyer->id,
					$this->currentDatetime->format('Y-m-d'),
					$this->currentDatetime->format('Y-m-d'),
					$new_order->get('coupon_id'),
				];
				$params[0] .= ($buyer->merchant_id ? " = {$buyer->merchant_id}" : ' IS NULL') . ' GROUP BY a.id';
				$coupon = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if ($coupon &&
					(($coupon->maximum_usage && $coupon->total_usage < $coupon->maximum_usage) &&
					(!$coupon->multiple_use && $coupon->personal_usage < 1) &&
					($coupon->minimum_purchase && $order->original_bill >= $coupon->minimum_purchase))) {
					$order->coupon_id   = $coupon->id;
					$order->discount   += $coupon->discount_type ? $coupon->price_discount : ceil($coupon->price_discount * $order->original_bill / 100.0);
					$order->final_bill -= $order->discount;
				}
			}
			$query = <<<QUERY
				SELECT
					a.id,
					a.open_on_sunday,
					a.open_on_monday,
					a.open_on_tuesday,
					a.open_on_wednesday,
					a.open_on_thursday,
					a.open_on_friday,
					a.open_on_saturday,
					a.business_opening_hour,
					a.business_closing_hour,
					a.delivery_hours
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN coverage_area c ON a.id = c.user_id
					JOIN user_product d ON a.id = d.user_id
					JOIN products e ON d.product_id = e.id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$buyer->village->id} AND
QUERY;
			if ($buyer->merchant_id) {
				$query .= " a.premium_merchant = 1 AND a.id = {$buyer->merchant_id}";
			} else {
				$query .= ' a.premium_merchant IS NULL AND a.id IN(' . $new_order->get('cart')->keys()->join(',') . ')';
			}
			$query .= ' GROUP BY a.id';
			$result = $this->db->query($query);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$delivery_hours = preg_split('/,/', $item->delivery_hours, -1, PREG_SPLIT_NO_EMPTY);
				if (!$delivery_hours) {
					continue;
				}
				$delivery_datetimes[$item->id] = [];
				foreach ($periods as $date) {
					if ($date == $current_datetime && $current_hour >= max($delivery_hours)) {
						continue;
					}
					foreach ($delivery_hours as $hour) {
						if ($date == $current_datetime && $hour < $minimum_hour) {
							continue;
						}
						$date->setTime($hour, 0);
						$delivery_datetimes[$item->id][] = $date->format('Y-m-d H:i:s');
					}
				}
			}
			$total_merchants = count(array_keys($delivery_datetimes));
			if ($total_merchants > 1) {
				$delivery_datetimes = call_user_func_array('array_intersect', array_values($delivery_datetimes));
			} else {
				$delivery_datetimes = array_shift($delivery_datetimes);
			}
			if (!$new_order->hasKey('scheduled_delivery') || !in_array($new_order->get('scheduled_delivery'), $delivery_datetimes)) {
				$new_order->put('scheduled_delivery', $delivery_datetimes[0]);
				$this->session->set('order', serialize($new_order));
			}
			$order->scheduled_delivery = $new_order->get('scheduled_delivery');
		}
		$params = [<<<QUERY
			SELECT
				a.id,
				a.code,
				a.price_discount,
				a.discount_type,
				a.multiple_use,
				a.minimum_purchase,
				a.maximum_usage,
				COUNT(DISTINCT b.id) AS personal_usage,
				COUNT(DISTINCT c.id) AS total_usage
			FROM
				coupons a
				LEFT JOIN orders b ON a.id = b.coupon_id AND b.status != '-1' AND b.buyer_id = ?
				LEFT JOIN orders c ON a.id = c.coupon_id AND c.status != '-1'
			WHERE
				a.status = '1' AND
				a.effective_date <= ? AND
				a.expiry_date > ? AND
				a.user_id
QUERY
			,
			$buyer->id,
			$this->currentDatetime->format('Y-m-d'),
			$this->currentDatetime->format('Y-m-d'),
		];
		$params[0] .= ($buyer->merchant_id ? " = {$buyer->merchant_id}" : ' IS NULL') . ' GROUP BY a.id ORDER BY a.code';
		$result     = $this->db->query(array_shift($params), $params);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			if (($row->maximum_usage && $row->total_usage >= $row->maximum_usage) ||
				(!$row->multiple_use && $row->personal_usage >= 1)) {
				continue;
			}
			$coupons[] = $row;
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			if ($item->picture) {
				$item->thumbnails = explode(',', $item->thumbnails);
			}
			$products[] = $item;
		}
		$this->view->order               = $order;
		$this->view->order_products      = $order_products;
		$this->view->buyer               = $buyer;
		$this->view->product_categories  = $product_categories;
		$this->view->delivery_datetimes  = $delivery_datetimes;
		$this->view->coupons             = $coupons;
		$this->view->products            = $products;
		$this->view->product_category_id = $product_category_id;
		$this->view->keyword             = $keyword;
		$this->view->page                = $page;
		$this->view->pages               = $pages;
	}

	function addProductAction() {
		if (!$this->request->isPost() || !($buyer_id = $this->dispatcher->getParam('buyer_id', 'int')) || !($buyer = User::findFirst(['status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [Role::BUYER, $buyer_id]]))) {
			$this->flashSession->error('Pembeli tidak ditemukan.');
			return $this->response->redirect('/admin/orders');
		}
		try {
			$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
			$keyword             = $this->dispatcher->getParam('keyword');
			$page                = $this->dispatcher->getParam('page', 'int');
			$user_product_id     = $this->request->getPost('user_product_id', 'int');
			$quantity            = max($this->request->getPost('quantity', 'int'), 0);
			$query               = <<<QUERY
				SELECT
					b.user_id,
					b.price,
					b.stock
				FROM
					products a
					JOIN user_product b ON a.id = b.product_id
					JOIN users c ON b.user_id = c.id
					JOIN coverage_area d ON c.id = d.user_id
				WHERE
					d.village_id = {$buyer->village_id}
					AND a.published = 1
					AND b.published = 1
					AND b.price > 0
					AND b.stock > 0
					AND c.status = 1
					AND b.id = {$user_product_id}
QUERY;
			if ($buyer->merchant_id) {
				$query .= " AND b.user_id = {$buyer->merchant_id}";
			}
			if ($product_category_id) {
				$query .= " AND a.product_category_id = {$product_category_id}";
			}
			if ($keyword) {
				$query .= " AND a.name ILIKE '%{$keyword}%'";
			}
			$query .= ' LIMIT 1 OFFSET 0';
			if (!$user_product = $this->db->fetchOne($query, Db::FETCH_OBJ)) {
				throw new Exception('Produk tidak ditemukan.');
			}
			if ($this->session->has('order')) {
				$new_order = unserialize($this->session->get('order'));
				if ($new_order->hasKey('buyer_id') && $new_order->get('buyer_id') != $buyer->id) {
					$new_order->get('cart')->clear();
				}
			} else {
				$new_order = new Map;
				$new_order->put('cart', new Map);
			}
			if (!$new_order->get('cart')->hasKey($user_product->user_id)) {
				$new_order->get('cart')->put($user_product->user_id, new Map);
			}
			$new_order->get('cart')->get($user_product->user_id)->put($user_product_id, min($quantity, $user_product->stock));
			$new_order->put('buyer_id', $buyer->id);
			$this->session->set('order', serialize($new_order));
			$this->flashSession->success('Produk berhasil ditambahkan.');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/orders/create/buyer_id:{$buyer->id}" . ($product_category_id ? "/product_category_id:{$product_category_id}" : '') . ($keyword ? "/keyword:{$keyword}" : '') . ($page && $page > 1 ? "/page:{$page}" : ''));
		}
	}

	function removeProductAction() {
		if (!$this->request->isPost() || !($buyer_id = $this->dispatcher->getParam('buyer_id', 'int')) || !($buyer = User::findFirst(['status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [Role::BUYER, $buyer_id]]))) {
			$this->flashSession->error('Pembeli tidak ditemukan.');
			return $this->response->redirect('/admin/orders');
		}
		$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword             = $this->dispatcher->getParam('keyword');
		$page                = $this->dispatcher->getParam('page', 'int');
		$user_product_id     = $this->request->getPost('user_product_id', 'int');
		if ($this->session->has('order')) {
			$new_order = unserialize($this->session->get('order'));
			$query     = <<<QUERY
				SELECT
					b.id,
					b.user_id,
					b.price,
					b.stock
				FROM
					products a
					JOIN user_product b ON a.id = b.product_id
					JOIN users c ON b.user_id = c.id
					JOIN coverage_area d ON c.id = d.user_id
				WHERE
					d.village_id = {$buyer->village_id}
					AND a.published = 1
					AND b.published = 1
					AND b.price > 0
					AND b.stock > 0
					AND c.status = 1
					AND b.id = {$user_product_id}
QUERY;
			if ($buyer->merchant_id) {
				$query .= " AND b.user_id = {$buyer->merchant_id}";
			}
			if ($product_category_id) {
				$query .= " AND a.product_category_id = {$product_category_id}";
			}
			if ($keyword) {
				$query .= " AND a.name ILIKE '%{$keyword}%'";
			}
			$query .= ' LIMIT 1 OFFSET 0';
			if ($user_product = $this->db->fetchOne($query, Db::FETCH_OBJ)) {
				if ($new_order->get('buyer_id') != $buyer->id) {
					$new_order->put('buyer_id', $buyer->id);
					$new_order->get('cart')->clear();
				} else if ($new_order->get('cart')->hasKey($user_product->user_id) && $new_order->get('cart')->get($user_product->user_id)->hasKey(strval($user_product->id))) {
					$new_order->get('cart')->get($user_product->user_id)->remove(strval($user_product->id));
					if ($new_order->get('cart')->get($user_product->user_id)->isEmpty()) {
						$new_order->get('cart')->remove($user_product->user_id);
					}
					if ($new_order->get('cart')->isEmpty() && $new_order->hasKey('coupon_id')) {
						$new_order->remove('coupon_id');
					}
				}
			}
			$this->session->set('order', serialize($new_order));
		}
		return $this->response->redirect("/admin/orders/create/buyer_id:{$buyer->id}" . ($product_category_id ? "/product_category_id:{$product_category_id}" : '') . ($keyword ? "/keyword:{$keyword}" : '') . ($page && $page > 1 ? "/page:{$page}" : ''));
	}

	function applyCouponAction() {
		if (!$this->request->isPost() || !($buyer_id = $this->dispatcher->getParam('buyer_id', 'int')) || !($buyer = User::findFirst(['status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [Role::BUYER, $buyer_id]]))) {
			$this->flashSession->error('Pembeli tidak ditemukan.');
			return $this->response->redirect('/admin/orders');
		}
		$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword             = $this->dispatcher->getParam('keyword');
		$page                = $this->dispatcher->getParam('page', 'int');
		$coupon_id           = $this->request->getPost('coupon_id', 'int');
		if ($this->session->has('order')) {
			$new_order = unserialize($this->session->get('order'));
			if ($new_order->get('buyer_id') != $buyer->id) {
				$new_order->put('buyer_id', $buyer->id);
				$new_order->get('cart')->clear();
			}
			if (!$coupon_id) {
				$new_order->remove('coupon_id');
			} else {
				$params = [<<<QUERY
					SELECT
						a.id,
						a.code,
						a.price_discount,
						a.discount_type,
						a.multiple_use,
						a.minimum_purchase,
						a.maximum_usage,
						COUNT(DISTINCT b.id) AS personal_usage,
						COUNT(DISTINCT c.id) AS total_usage
					FROM
						coupons a
						LEFT JOIN orders b ON a.id = b.coupon_id AND b.status != '-1' AND b.buyer_id = ?
						LEFT JOIN orders c ON a.id = c.coupon_id AND c.status != '-1'
					WHERE
						a.status = '1' AND
						a.effective_date <= ? AND
						a.expiry_date > ? AND
						a.id = ? AND
						a.user_id
QUERY
					,
					$buyer->id,
					$this->currentDatetime->format('Y-m-d'),
					$this->currentDatetime->format('Y-m-d'),
					$coupon_id,
				];
				$params[0] .= ($buyer->merchant_id ? " = {$buyer->merchant_id}" : ' IS NULL') . ' GROUP BY a.id';
				$coupon = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if ($coupon &&
					(($coupon->maximum_usage && $coupon->total_usage < $coupon->maximum_usage) &&
					(!$coupon->multiple_use && $coupon->personal_usage < 1))) {
					$new_order->put('coupon_id', $coupon->id);
				}
			}
			$this->session->set('order', serialize($new_order));
		}
		return $this->response->redirect("/admin/orders/create/buyer_id:{$buyer->id}" . ($product_category_id ? "/product_category_id:{$product_category_id}" : '') . ($keyword ? "/keyword:{$keyword}" : '') . ($page && $page > 1 ? "/page:{$page}" : ''));
	}

	function setDeliveryAction() {
		if (!$this->request->isPost() || !($buyer_id = $this->dispatcher->getParam('buyer_id', 'int')) || !($buyer = User::findFirst(['status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [Role::BUYER, $buyer_id]]))) {
			$this->flashSession->error('Pembeli tidak ditemukan.');
			return $this->response->redirect('/admin/orders');
		}
		$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword             = $this->dispatcher->getParam('keyword');
		$page                = $this->dispatcher->getParam('page', 'int');
		$scheduled_delivery  = $this->request->getPost('scheduled_delivery');
		if ($this->session->has('order')) {
			$new_order = unserialize($this->session->get('order'));
			if ($new_order->get('buyer_id') != $buyer->id) {
				$new_order->put('buyer_id', $buyer->id);
				$new_order->cart->clear();
			}
			if ($scheduled_delivery) {
				$new_order->put('scheduled_delivery', $scheduled_delivery);
				$this->session->set('order', serialize($new_order));
			}
		}
		return $this->response->redirect("/admin/orders/create/buyer_id:{$buyer->id}" . ($product_category_id ? "/product_category_id:{$product_category_id}" : '') . ($keyword ? "/keyword:{$keyword}" : '') . ($page && $page > 1 ? "/page:{$page}" : ''));
	}
}