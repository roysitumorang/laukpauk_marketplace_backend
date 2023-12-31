<?php

namespace Application\Backend\Controllers;

use Application\Models\ProductCategory;
use Ds\Set;
use Exception;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ProductCategoriesController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Products');
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->dispatcher->getParam('keyword');
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
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
				'total_products' => 'COUNT(DISTINCT b.id)',
			])
			->from(['a' => 'Application\Models\ProductCategory'])
			->leftJoin('Application\Models\Product', 'a.id = b.product_category_id', 'b')
			->groupBy('a.id')
			->orderBy('a.name ASC');
		if ($keyword) {
			$builder->where('a.name ILIKE ?0', ["%{$keyword}%"]);
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page       = $paginator->getPaginate();
		$pages      = $this->_setPaginationRange($page);
		$categories = new Set;
		foreach ($page->items as $item) {
			$category = ProductCategory::findFirst($item->id);
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('thumbnail', $category->thumbnail(120));
			$categories->add($item);
		}
		$this->view->keyword    = $keyword;
		$this->view->page       = $page;
		$this->view->pages      = $pages;
		$this->view->categories = $categories;
		$this->_prepare_datas();
	}

	function createAction() {
		$category = new ProductCategory;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($category);
			if ($category->validation() && $category->create()) {
				$this->flashSession->success('Penambahan kategori berhasil.');
				return $this->response->redirect('/admin/product_categories');
			}
			$this->flashSession->error('Penambahan kategori tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas($category);
	}

	function updateAction($id) {
		$category = ProductCategory::findFirst($id);
		if (!$category) {
			$this->flashSession->error('Kategori tidak ditemukan.');
			return $this->dispatcher->forward('product_categories');
		}
		$old_name = $category->name;
		$category->thumbnail = $category->thumbnail(120);
		if ($this->request->isPost()) {
			$this->_set_model_attributes($category);
			if ($category->validation() && $category->update()) {
				$old_name != $category->name && $this->db->execute("UPDATE products a SET keywords = TO_TSVECTOR('simple', b.name || ' ' || a.name) FROM product_categories b WHERE a.product_category_id = b.id AND b.id = ?", [$category->id]);
				$this->flashSession->success('Update kategori berhasil.');
				return $this->response->redirect("/admin/product_categories/{$category->id}/update");
			}
			$this->flashSession->error('Update kategori tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas($category);
	}

	function toggleStatusAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst($id);
			$category ? $category->update(['published' => $category->published ? 0 : 1]) : $this->flashSession->error('Kategori tidak ditemukan!');
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function deletePictureAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['id = ?0 AND picture IS NOT NULL', 'bind' => [$id]]);
			if ($category) {
				$category->deletePicture();
				return $this->response->redirect("/admin/product_categories/{$category->id}/update");
			}
			$this->flashSession->error('Kategori tidak ditemukan!');
		}
		return $this->response->redirect('/admin/product_categories');
	}

	function deleteAction($id) {
		try {
			if (!$category = ProductCategory::findFirst($id)) {
				throw new Exception('Kategori tidak ditemukan.');
			}
			$category->delete();
			$this->flashSession->success('Kategori berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/product_categories');
		}
	}

	private function _prepare_datas(ProductCategory $category = null) {
		$this->view->category = $category;
	}

	private function _set_model_attributes(&$category) {
		$category->setName($this->request->getPost('name'));
		$category->setNewPermalink($this->request->getPost('new_permalink'));
		$category->setPublished($this->request->getPost('published'));
		$category->setDescription($this->request->getPost('description'));
		$category->setMetaTitle($this->request->getPost('meta_title'));
		$category->setMetaDesc($this->request->getPost('meta_desc'));
		$category->setMetaKeyword($this->request->getPost('meta_keyword'));
		$category->setNewPicture($_FILES['picture']);
	}
}