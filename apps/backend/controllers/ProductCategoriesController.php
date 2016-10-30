<?php

namespace Application\Backend\Controllers;

use Application\Models\ProductCategory;
use Application\Models\Product;
use Exception;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use stdClass;

class ProductCategoriesController extends BaseController {
	private $_thumb;

	function initialize() {
		parent::initialize();
		$this->_thumb                  = new stdClass;
		$this->_thumb->width           = 120;
		$this->_thumb->height          = 120;
		$this->_thumb->default_picture = null;
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->request->getQuery('keyword', 'string');
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'id'             => 'a.id',
				'parent_id'      => 'a.parent_id',
				'a.name',
				'permalink'      => 'a.permalink',
				'picture'        => 'a.picture',
				'published'      => 'a.published',
				'description'    => 'a.description',
				'meta_title'     => 'a.meta_title',
				'meta_desc'      => 'a.meta_desc',
				'meta_keyword'   => 'a.meta_keyword',
				'created_by'     => 'a.created_by',
				'created_at'     => 'a.created_at',
				'updated_by'     => 'a.updated_by',
				'updated_at'     => 'a.updated_at',
				'total_children' => 'COUNT(DISTINCT b.id)',
				'total_products' => 'COUNT(DISTINCT c.id)',
			])
			->from(['a' => 'Application\Models\ProductCategory'])
			->leftJoin('Application\Models\ProductCategory', 'a.id = b.parent_id', 'b')
			->leftJoin('Application\Models\Product', 'a.id = c.product_category_id', 'c')
			->groupBy('a.id')
			->orderBy('id DESC');
		if ($keyword) {
			$builder->where('a.name LIKE :name:', ['name' => "%{$keyword}%"]);
		}
		$paginator  = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page       = $paginator->getPaginate();
		$pages      = $this->_setPaginationRange($page);
		$categories = [];
		foreach ($page->items as $item) {
			$category  = ProductCategory::findFirst($item->id);
			$thumbnail = $category->getThumbnail($this->_thumb->width, $this->_thumb->height, $this->_thumb->default_picture);
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('thumbnail', $thumbnail);
			$categories[] = $item;
		}
		$this->view->menu                     = $this->_menu('Products');
		$this->view->product_category_keyword = $keyword;
		$this->view->categories               = $categories;
		$this->view->page                     = $paginator->getPaginate();
		$this->view->pages                    = $pages;
	}

	function showAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($category = ProductCategory::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('product_categories');
		}
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
				return $this->response->redirect('/admin/product_categories');
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
		$category->thumbnail = $category->getThumbnail($this->_thumb->width, $this->_thumb->height, $this->_thumb->default_picture);
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('delete_picture')) {
				$category->deletePicture();
				return $this->response->redirect("/admin/product_categories/update/{$category->id}");
			}
			if ($this->dispatcher->hasParam('published')) {
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

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($category = ProductCategory::findFirst($id))) {
				throw new Exception('Data tidak ditemukan.');
			}
			if (ProductCategory::findFirstByParentId($category->id) || Product::findFirstByProductCategoryId($category->id)) {
				throw new Exception('Data tidak dapat dihapus karena memilik sub kategori / product');
			}
			$category->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/product_categories');
		}
	}
}