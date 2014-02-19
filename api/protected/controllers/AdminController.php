<?php

class AdminController extends Controller {


	public function actionIndex(){
		$this->layout = 'admin';
		if (!Yii::app()->user->checkAccess("isAdmin")) {
			$this->render('login');
		}
		else {
			$this->render('index');
		}
	}
}
