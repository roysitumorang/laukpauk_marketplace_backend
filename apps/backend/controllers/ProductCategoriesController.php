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
						->columns("c.id, c.parent_id, c.name, c.permalink, c.picture, c.published, c.description, c.meta_title, c.meta_desc, c.meta_keyword, c.created_by, c.created_at, c.updated_by, c.updated_at, NULL AS rank, CONCAT(t.id, '.jpg') AS thumbnail, COUNT(DISTINCT p.id) AS total_products, COUNT(DISTINCT s.id) AS total_children")
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
	}

	function showAction(ProductCategory $category) {
		$this->view->category = $category;
	}

	function newAction() {
		if ($this->request->isGet()) {
			$category  = new ProductCategory;
			$parent_id = $this->request->getQuery('parent_id', 'int');
			if ($parent_id && ProductCategory::findFirst($parent_id)) {
				$category->parent_id = $parent_id;
			}
			$this->view->category = $category;
		}
		$this->view->menu = $this->_menu('Products');
	}

	function createAction() {
		$category = new ProductCategory;
		$data     = $this->request->getPost();
		$category->setParentId($data['parent_id'])
			->setName($data['name'])
			->setPermalink($data['permalink'])
			->setPublished($data['published'])
			->setDescription($data['description'])
			->setMetaTitle($data['meta_title'])
			->setMetaDesc($data['meta_desc'])
			->setMetaKeyword($data['meta_keyword'])
			->setPicture($this->request->getUploadedFiles()[0]);
		if ($category->getMessages() || !$category->create()) {
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
			$this->view->category = $category;
			return $this->dispatcher->forward([
				'controller' => 'product_categories',
				'action'     => 'new',
			]);
		}
		$this->flashSession->success('Penambahan data berhasil.');
		return $this->response->redirect("/admin/product_categories/edit/{$category->id}");
	}

	function editAction(int $id) {
		if ($this->request->isGet()) {
			$category             = ProductCategory::findFirst($id);
			if ($category->picture && !$category->thumbnail) {
				$category->thumbnail = Thumbnail::generate('product_category', $category->id, $category->picture, 120, 120)->name();
			}
			$this->view->category = $category;
		}
		$this->view->menu = $this->_menu('Products');
	}

	function updateAction(int $id) {
		$category = ProductCategory::findFirst($id);
		$data     = $this->request->getPost();
		$category->setName($data['name'])
			->setPermalink($data['permalink'])
			->setPublished($data['published'])
			->setDescription($data['description'])
			->setMetaTitle($data['meta_title'])
			->setMetaDesc($data['meta_desc'])
			->setMetaKeyword($data['meta_keyword'])
			->setPicture($this->request->getUploadedFiles()[0]);
		if (!$category->update()) {
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
			$this->view->category = $category;
			return $this->dispatcher->forward([
				'controller' => 'product_categories',
				'action'     => 'edit',
				'id'         => $category->id,
			]);
		}
		$this->flashSession->success('Update data berhasil.');
		return $this->response->redirect("/admin/product_categories/edit/{$category->id}");
	}

	function deleteAction() {}

	function publishAction() {}

	function unpublishAction() {}

	function deletePictureAction() {}
}