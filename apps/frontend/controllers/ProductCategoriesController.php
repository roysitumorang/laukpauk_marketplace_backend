<?php

namespace Application\Frontend\Controllers;

use Application\Models\ProductCategory;
use Phalcon\Exception;
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
				'a.permalink',
				'a.picture',
				'a.published',
				'a.description',
				'a.meta_title',
				'a.meta_desc',
				'a.meta_keyword',
				'a.created_by',
				'a.created_at',
				'a.updated_by',
				'a.updated_at',
				'total_products' => 'COUNT(DISTINCT b.id)',
			])
			->from(['a' => 'Application\Models\ProductCategory'])
			->leftJoin('Application\Models\Product', 'a.id = b.product_category_id', 'b')
			->groupBy('a.id')
			->orderBy('a.name ASC');
		$builder->where("a.user_id = {$this->currentUser->id}");
		if ($keyword) {
			$builder->andWhere('a.name ILIKE ?0', ["%{$keyword}%"]);
		}
		$paginator  = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page       = $paginator->getPaginate();
		$pages      = $this->_setPaginationRange($page);
		$categories = [];
		foreach ($page->items as $item) {
			$category = ProductCategory::findFirst($item->id);
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('thumbnail', $category->thumbnail(120));
			$categories[] = $item;
		}
		$this->view->keyword    = $keyword;
		$this->view->categories = $categories;
		$this->view->page       = $paginator->getPaginate();
		$this->view->pages      = $pages;
		$this->_prepare_datas();
	}

	function createAction() {
		$category = new ProductCategory;
		if ($this->request->isPost()) {
			$category->user_id = $this->currentUser->id;
			$this->_set_model_attributes($category);
			if ($category->validation() && $category->create()) {
				$this->flashSession->success('Penambahan kategori berhasil.');
				return $this->response->redirect('/product_categories');
			}
			$this->flashSession->error('Penambahan kategori tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas($category);
	}

	function updateAction($id) {
		$category = ProductCategory::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]]);
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
				return $this->response->redirect("/product_categories/{$category->id}/update");
			}
			$this->flashSession->error('Update kategori tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas($category);
	}

	function publishAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['user_id = ?0 AND id = ?1 AND published = 0', 'bind' => [$this->currentUser->id, $id]]);
			if ($category) {
				$category->update(['published' => 1]);
			} else {
				$this->flashSession->error('Kategori tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function unpublishAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['user_id = ?0 AND id = ?1 AND published = 1', 'bind' => [$this->currentUser->id, $id]]);
			if ($category) {
				$category->update(['published' => 0]);
			} else {
				$this->flashSession->error('Kategori tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function deletePictureAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['user_id = ?0 AND id = ?1 AND picture IS NOT NULL', 'bind' => [$this->currentUser->id, $id]]);
			if ($category) {
				$category->deletePicture();
				return $this->response->redirect("/product_categories/{$category->id}/update");
			}
			$this->flashSession->error('Kategori tidak ditemukan!');
		}
		return $this->response->redirect('/product_categories');
	}

	function deleteAction($id) {
		try {
			if (!$category = ProductCategory::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]])) {
				throw new Exception('Kategori tidak ditemukan.');
			}
			$category->delete();
			$this->flashSession->success('Kategori berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/product_categories');
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