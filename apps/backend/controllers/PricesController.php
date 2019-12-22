<?php

namespace Application\Backend\Controllers;

use Application\Models\UserProduct;
use Phalcon\Paginator\Adapter\QueryBuilder;

class PricesController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'c.company',
				'b.name',
				'b.stock_unit',
				'a.price',
			])
			->from(['a' => 'Application\Models\UserProduct'])
			->join('Application\Models\Product', 'a.product_id = b.id', 'b')
			->join('Application\Models\User', 'a.user_id = c.id', 'c')
			->orderBy('b.name, b.stock_unit, c.company')
			->where('c.status = 1');
		if ($keyword) {
			$keyword_placeholder = "%{$keyword}%";
			$builder->andWhere('b.name ILIKE ?0', [$keyword_placeholder]);
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page     = $paginator->paginate();
		$pages    = $this->_setPaginationRange($page);
		$products = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$products[] = $item;
		}
		$this->view->menu     = $this->_menu('Products');
		$this->view->page     = $page;
		$this->view->pages    = $pages;
		$this->view->keyword  = $keyword;
		$this->view->products = $products;
	}

	function updateAction() {
		$prices = $this->request->getPost('prices');
		if ($this->request->isPost()) {
			foreach ($prices as $id => $price) {
				if (($user_product = UserProduct::findFirst($id)) && filter_var($price, FILTER_VALIDATE_INT)) {
					$user_product->update(['price' => $price]);
				}
			}
			$this->flashSession->success('Update harga berhasil.');
		}
		$this->dispatcher->forward(['action' => 'index']);
	}
}