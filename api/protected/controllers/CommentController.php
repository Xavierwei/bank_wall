<?php

class CommentController extends Controller {
  
  public function actionIndex() {
    $this->responseError("not suppoort");
  }
  
  public function actionPost() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $nid = $request->getPost("nid");
    
    //$uid = Yii::app()->user->getId();
    $uid = UserAR::model()->find()->uid;
    
    $content = $request->getPost("content");
    
    $commentAr = new CommentAR();
    $commentAr->attributes = array(
        "uid" => $uid,
        "nid" => $nid,
        "content" => $content
    );
    
    if ($commentAr->validate()) {
      $commentAr->save();
      $this->responseJSON($commentAr->attributes, "success");
    }
    else {
      $this->responseError(current(array_shift($commentAr->getErrors())));
    }
  }
  
  public function actionDelete() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    // TODO:: 权限检查
    
    $cid = $request->getPost("cid");
    if ($cid) {
      $commentAr = new CommentAR();
      $commentAr->deleteByPk($cid);
      
      $this->responseJSON(array(), "success");
    }
    else {
      $this->responseError("invalid params");
    }
  }
  
  public function actionList() {
    $request = Yii::app()->getRequest();
    
    $nid = $request->getParam("nid");
    
    $query = new CDbCriteria();
    if ($nid) {
      $query->addCondition("nid=:nid");
      $query->params[":nid"] = $nid;
    }
    
    $query->with = array("user");
    
    $comments = CommentAR::model()->findAll($query);
    
    $retdata = array();
    
    foreach ($comments as $comment) {
      $commentdata = $comment->attributes;
      
      // 加载 评论相关的用户资料
      $country = $comment->user->country;
      $user = $comment->user->attributes;
      $user["country"] = $country->attributes;
      $commentdata["user"] = $comment->user->getOutputRecordInArray($user);
      
      $retdata[] = $commentdata;
    }
    
    $this->responseJSON($retdata, "success");
  }
  
  public function actionSearchByKeyword() {
    $request = Yii::app()->getRequest();
    
    $keyword = $request->getParam("keyword");
    $page = $request->getParam("page");
    $pagenum = $request->getParam("pagenum");
    $orderby = $request->getParam("orderby");
    
    // 所有的参数都是必须的
    if (!$keyword && !$page && !$pagenum && !$orderby) {
      $this->responseError("invalid params");
    }
    
    // orderby 允许 [datetime | nid ]
    if (!in_array($orderby, array("datetime", "nid"))) {
      $this->responseError("invalid params");
    }
    
    // pagenum / page 是数字
    if (!is_numeric($pagenum) && !is_numeric($page)) {
      $this->responseError("invalid params");
    }
    
    $query = new CDbCriteria();
    $query->limit = $pagenum;
    // 从第一也计算起
    $query->offset = ( $page - 1 ) * $pagenum;
    $query->order = "{$orderby} DESC";
    
    // 关键字搜索
    $query->addSearchCondition("content", $keyword);
    
    $comments = CommentAR::model()->findAll($query);
    
    $retdata = array();
    foreach ($comments as $comment) {
      $commentdata = $comment->attributes;
      
      // 加载 评论相关的用户资料
      $country = $comment->user->country;
      $user = $comment->user->attributes;
      $user["country"] = $country->attributes;
      $commentdata["user"] = $comment->user->getOutputRecordInArray($user);
      
      $retdata[] = $commentdata;
    }
    
    $this->responseJSON($retdata, "success");
  }
}

