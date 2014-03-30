<?php

class HttpRequest extends CHttpRequest
{

	public function validateCsrfToken($event)
	{
		if($this->getIsPostRequest())
		{
			$valid = true;
			$uid = Yii::app()->user->getId();
			$user = UserAR::model()->findByPk($uid);

			// Validate CSRF token
			$token = Drtool::getMyCookie('sg_token');
			if($token != $user->token) {
				$valid = false;
			}

			if(!$valid)
				throw new CHttpException(400,'The CSRF token could not be verified.');
		}
	}
}