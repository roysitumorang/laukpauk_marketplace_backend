<?php

namespace Application\Api\V3\Buyer;

use Ds\Set;
use Application\Models\Feedback;

class FeedbackController extends ControllerBase {
	function createAction() {
		$feedback             = new Feedback;
		$feedback->content    = $this->_post->content;
		$feedback->user       = $this->_current_user;
		$feedback->created_at = $this->currentDatetime->format('Y-m-d H:i:s');
		if ($feedback->validation() && $feedback->create()) {
			$this->_response['status']  = 1;
			$this->_response['message'] = 'Terima kasih!<br>Feedback telah disimpan.';
		} else {
			$errors = new Set;
			foreach ($feedback->getMessages() as $error) {
				$errors->add($error->getMessage());
			}
			$this->_response['message'] = $errors->join('<br>');
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}