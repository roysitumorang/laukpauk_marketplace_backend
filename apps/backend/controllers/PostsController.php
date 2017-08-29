<?php

namespace Application\Backend\Controllers;

use Application\Models\Post;
use Ds\Set;

class PostsController extends ControllerBase {
	function indexAction() {
		$posts  = new Set;
		$errors = 0;
		foreach (Post::find(['order' => 'id']) as $post) {
			if ($this->request->isPost()) {
				$post->setBody($this->request->getPost('body')[$post->id]);
				if (!$post->validation() || !$post->update()) {
					foreach ($post->getMessages() as $error) {
						$this->flashSession->error($error);
						$errors++;
					}
				}
			}
			$posts->add($post);
		}
		if ($this->request->isPost() && !$errors) {
			$this->flashSession->success('Update konten berhasil.');
		}
		$this->view->menu  = $this->_menu('Content');
		$this->view->posts = $posts;
	}
}