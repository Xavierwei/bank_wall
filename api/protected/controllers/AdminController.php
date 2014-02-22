<?php

class AdminController extends Controller {


	public function actionIndex(){
		$this->layout = 'admin';
		if (!Yii::app()->user->checkAccess("isAdmin")) {
			$this->render('login');
		}
		else {
			$token = UserAR::model()->getToken();
			$this->render('index', array('token'=>$token));
		}
	}
}
