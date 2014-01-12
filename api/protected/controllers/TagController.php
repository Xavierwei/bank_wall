<?php

class TagController extends Controller {
  
  // 
  public function actionIndex() {
    $this->responseError("not support yet");
  }
  
  public function actionList() {
    $request = Yii::app()->getRequest();
    
    if (!Yii::app()->user->checkAccess("flagNode")) {
      return $this->responseError("permission deny");
    }
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $nid = $request->getPost("nid");
    $cid = $request->getPost("cid");
    
    //2者任意有一个
    if (!$nid && !$cid) {
      $this->responseError("invalid params");
    }
    

  }
  

}

