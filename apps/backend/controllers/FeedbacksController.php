<?php

namespace Application\Backend\Controllers;

use Application\Models\Feedback;
use Phalcon\Paginator\Adapter\QueryBuilder;

class FeedbacksController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
				->from(Feedback::class)
				->orderBy('id DESC');
		$paginator    = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page      = $paginator->paginate();
		$pages     = $this->_setPaginationRange($page);
		$feedbacks = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$feedbacks[] = $item;
		}
		$this->view->menu      = $this->_menu('Mailbox');
		$this->view->feedbacks = $feedbacks;
		$this->view->page      = $page;
		$this->view->pages     = $pages;
	}
}