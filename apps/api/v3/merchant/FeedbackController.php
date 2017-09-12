<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Feedback;
use Ds\Set;
use Exception;

class FeedbackController extends ControllerBase {
	function createAction() {
		try {
			$feedback             = new Feedback;
			$feedback->content    = $this->post->content;
			$feedback->created_by = $this->currentUser->id;
			$feedback->created_at = $this->currentDatetime->format('Y-m-d H:i:s');
			if ($feedback->validation() && $feedback->create()) {
				$this->_response['status']  = 1;
				throw new Exception('Terima kasih!<br>Feedback telah disimpan.');
			}
			$errors = new Set;
			foreach ($feedback->getMessages() as $error) {
				$errors->add($error->getMessage());
			}
			throw new Exception($errors->join('<br>'));
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}