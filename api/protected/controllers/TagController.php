<?php

class TagController extends Controller {
  
  // 
  public function actionIndex() {
    $this->responseError("not support yet");
  }
  
  public function actionList() {
    $request = Yii::app()->getRequest();
    $term = $request->getParam("term");
    $retdata = TagAR::model()->searchTag($term);
    $this->responseJSON($retdata, "success");
  }

	public function actionAdd() {
		$request = Yii::app()->getRequest();
		$term = $request->getParam("term");
		$retdata = TagAR::model()->saveTag($term);
		$this->responseJSON($retdata, "success");
	}
  

}

