<?php

namespace Application\Backend\Controllers;

use Application\Models\ProductCategory;
use Application\Models\Thumbnail;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ProductCategoriesController extends BaseController {
	function indexAction() {
		$keyword      = $this->request->getQuery('keyword', 'string');
		$current_page = $this->request->getQuery('page', 'int') ?: 1;
		$thumb_width  = 120;
		$thumb_height = 120;
		$builder      = $this->modelsManager->createBuilder()
				->columns([
					'id'             => 'c.id',
					'parent_id'      => 'c.parent_id',
					'c.name',
					'permalink'      => 'c.permalink',
					'picture'        => 'c.picture',
					'published'      => 'c.published',
					'description'    => 'c.description',
					'meta_title'     => 'c.meta_title',
					'meta_desc'      => 'c.meta_desc',
					'meta_keyword'   => 'c.meta_keyword',
					'created_by'     => 'c.created_by',
					'created_at'     => 'c.created_at',
					'updated_by'     => 'c.updated_by',
					'updated_at'     => 'c.updated_at',
					'thumbnail'      => "CONCAT(t.id, '.jpg')",
					'total_products' => 'COUNT(DISTINCT p.id)',
					'total_children' => 'COUNT(DISTINCT s.id)',
				])
				->from(['c' => 'Application\Models\ProductCategory'])
				->leftJoin('Application\Models\Thumbnail', "t.reference_type = 'product_category' AND c.id = t.reference_id AND t.width = {$thumb_width} AND t.height = {$thumb_height}", 't')
				->leftJoin('Application\Models\Product', 'c.id = p.product_category_id', 'p')
				->leftJoin('Application\Models\ProductCategory', 'c.id = s.parent_id', 's')
				->groupBy('c.id')
				->orderBy('c.id DESC');
		if ($keyword) {
			$builder->where('c.name LIKE :name:', ['name' => "%{$keyword}%"]);
		}
		$limit        = $this->config->per_page;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page         = $paginator->getPaginate();
		foreach ($page->items as $i => $item) {
			if ($page->items[$i]->picture && !$page->items[$i]->thumbnail) {
				$page->items[$i]->thumbnail = Thumbnail::generate('product_category', $page->items[$i]->id, $page->items[$i]->picture, $thumb_width, $thumb_height)->name();
			}
			$page->items[$i]->rank           = ++$offset;
			$page->items[$i]->total_children = $this->db->fetchColumn("SELECT COUNT(1) FROM product_categories WHERE parent_id = {$page->items[$i]->id}") ?: 0;
		}
		$this->view->menu                     = $this->_menu('Products');
		$this->view->product_category_keyword = $keyword;
		$this->view->page                     = $paginator->getPaginate();
		$this->view->offset                   = $offset;
	}

	function showAction(ProductCategory $category) {
		$this->view->category = $category;
	}

	function createAction() {
		$category = new ProductCategory;
		if ($this->request->isPost()) {
			$category->setParentId($this->request->getPost('parent_id'));
			$category->setName($this->request->getPost('name'));
			$category->setNewPermalink($this->request->getPost('new_permalink'));
			$category->setPublished($this->request->getPost('published'));
			$category->setDescription($this->request->getPost('description'));
			$category->setMetaTitle($this->request->getPost('meta_title'));
			$category->setMetaDesc($this->request->getPost('meta_desc'));
			$category->setMetaKeyword($this->request->getPost('meta_keyword'));
			$category->setNewPicture($_FILES['picture']);
			if ($category->validation() && $category->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect("/admin/product_categories/update/{$category->id}");
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		} else {
			$parent_id = $this->request->getQuery('parent_id', 'int');
			if ($parent_id && ProductCategory::findFirst($parent_id)) {
				$category->parent_id = $parent_id;
			}
		}
		$this->view->category = $category;
		$this->view->menu     = $this->_menu('Products');
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($category = ProductCategory::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('product_categories');
		}
		if ($category->picture) {
			$category->thumbnail = Thumbnail::generate('product_category', $category->id, $category->picture, 120, 120)->name();
		}
		if ($this->request->isPost()) {
			if ($this->request->hasQuery('delete_picture')) {
				$category->deletePicture();
				return $this->response->redirect("/admin/product_categories/update/{$category->id}");
			}
			if ($this->request->hasQuery('published')) {
				$category->save(['published' => $category->published ? 0 : 1]);
				return $this->response->redirect($this->request->getQuery('next'));
			}
			$category->setName($this->request->getPost('name'));
			$category->setNewPermalink($this->request->getPost('new_permalink'));
			$category->setPublished($this->request->getPost('published'));
			$category->setDescription($this->request->getPost('description'));
			$category->setMetaTitle($this->request->getPost('meta_title'));
			$category->setMetaDesc($this->request->getPost('meta_desc'));
			$category->setMetaKeyword($this->request->getPost('meta_keyword'));
			$category->setNewPicture($_FILES['picture']);
			if ($category->validation() && $category->save()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/product_categories/update/{$category->id}");
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->category = $category;
		$this->view->menu     = $this->_menu('Products');
	}

	function deleteAction($id) {}
}