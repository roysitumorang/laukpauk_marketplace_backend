<?php

namespace Application\Backend\Controllers;

use Application\Models\PageCategory;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PageCategoriesController extends BaseController {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.has_create_page_menu',
				'a.has_picture_icon',
				'a.has_content',
				'a.has_url',
				'a.has_link_target',
				'a.has_rich_editor',
				'a.created_by',
				'a.created_at',
				'a.updated_by',
				'a.updated_at',
				'total_pages' => 'COUNT(b.id)',
			])
			->from(['a' => 'Application\Models\PageCategory'])
			->leftJoin('Application\Models\Page', 'a.id = b.page_category_id', 'b')
			->groupBy('a.id')
			->orderBy('a.name');
		$paginator       = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page            = $paginator->getPaginate();
		$pages           = $this->_setPaginationRange($page);
		$page_categories = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('removable', !$item->total_pages);
			$page_categories[] = $item;
		}
		$this->view->menu            = $this->_menu('Content');
		$this->view->page_categories = $page_categories;
		$this->view->page            = $page;
		$this->view->pages           = $pages;
	}

	function createAction() {
		$page_category = new PageCategory;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($page_category);
			if ($page_category->validation() && $page_category->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/page_categories');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($page_category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu          = $this->_menu('Content');
		$this->view->page_category = $page_category;
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($page_category = PageCategory::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('page_categories');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($page_category);
			if ($page_category->validation() && $page_category->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/page_categories');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($page_category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu          = $this->_menu('Content');
		$this->view->page_category = $page_category;
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($page_category = PageCategory::findFirst($id))) {
				throw new Exception('Data tidak ditemukan.');
			}
			$page_category->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/page_categories');
		}
	}

	private function _set_model_attributes(PageCategory &$page_category) {
		$page_category->setName($this->request->getPost('name'));
		$page_category->setHasCreatePageMenu($this->request->getPost('has_create_page_menu'));
		$page_category->setHasPictureIcon($this->request->getPost('has_picture_icon'));
		$page_category->setHasContent($this->request->getPost('has_content'));
		$page_category->setHasURL($this->request->getPost('has_url'));
		$page_category->setHasLinkTarget($this->request->getPost('has_link_target'));
		$page_category->setHasRichEditor($this->request->getPost('has_rich_editor'));
	}
}