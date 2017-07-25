<?php

namespace Application\Backend\Controllers;

use Application\Models\ProductCategory;
use Application\Models\User;
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
		$user_id      = $this->dispatcher->getParam('user_id', 'int');
		$keyword      = $this->dispatcher->getParam('keyword');
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.user_id',
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
			->where('a.user_id ' . ($user_id ? "= {$user_id}" : 'IS NULL'))
			->groupBy('a.id')
			->orderBy('a.name ASC');
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
		$this->view->user_id    = $user_id;
		$this->view->keyword    = $keyword;
		$this->view->categories = $categories;
		$this->view->page       = $paginator->getPaginate();
		$this->view->pages      = $pages;
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

	function publishAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['id = ?0 AND published = 0', 'bind' => [$id]]);
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
			$category = ProductCategory::findFirst(['id = ?0 AND published = 1', 'bind' => [$id]]);
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
		$this->view->category  = $category;
		$this->view->merchants = User::find([
			'premium_merchant = 1 AND status = 1',
			'columns' => 'id, company',
			'order'   => 'company'
		]);
	}

	private function _set_model_attributes(&$category) {
		$user_id = $this->request->getPost('user_id', 'int');
		if (!$category->id && $user_id && User::findFirst(['premium_merchant = 1 AND status = 1 AND id = ?0', 'bind' => [$user_id]])) {
			$category->user_id = $user_id;
		}
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