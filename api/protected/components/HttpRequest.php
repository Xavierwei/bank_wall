<?php

class HttpRequest extends CHttpRequest
{

	public function validateCsrfToken($event)
	{
		if($this->getIsPostRequest())
		{
			// exclude action postbymail
			if(strtolower($this->pathInfo) == 'node/postbymail') {
				return;
			}
			$valid = false;
			$uid = Yii::app()->user->getId();
			$user = UserAR::model()->findByPk($uid);

			// Validate CSRF token
			$token = Drtool::getMyCookie('sg_token');
			if(isset($user) && isset($token)) {
				if($token == $user->token) {
					$valid = true;
				}
			}

			if(!$valid)
				throw new CHttpException(400,'The CSRF token could not be verified.');
		}
	}
}