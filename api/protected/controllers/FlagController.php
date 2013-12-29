<?php

/**
 * @author jackey <jziwenchen@gmail.com>
 */
class FlagController extends Controller {
  
  // 
  public function actionIndex() {
    $this->responseError("not support yet");
  }
  
  public function actionPost() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $nid = $request->getPost("nid");
    $cid = $request->getPost("cid");
    
    //2者任意有一个
    if (!$nid && !$cid) {
      $this->responseError("invalid params");
    }
    
    // 如果 不是空 并且不是数字
    // 说明参数错误
    if ($nid && !is_numeric($nid)) {
      $this->responseError("invalid params");
    }
    if ($cid && !is_numeric($cid)) {
      $this->responseError("invalid params");
    }
    
    // 从session 拿到 uid
    // TODO:: 暂时 用任意用户uid 做测试, 登陆实现后 再完成此处功能
    //$uid = Yii::app()->user->getId();
    $uid = UserAR::model()->find()->uid;
    
    $flagAr = new FlagAR();
    $flagAr->uid = $uid;
    if ($nid) {
      $flagAr->nid = $nid;
    }
    if ($cid) {
      $flagAr->cid = $cid;
    }
    
    if ($flagAr->validate()) {
      $flagAr->save();
      
      $this->responseJSON($flagAr->attributes, "success");
    }
    else {
      $this->responseError(current(array_shift($flagAr->getErrors())));
    }
  }
  
  public function actionDeleteAll() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $nid = $request->getPost("nid");
    $cid = $request->getPost("cid");
    
    if (!$nid && !$cid) {
      return $this->responseError("invalid params");
    }
    
    $flagAr = new FlagAr();
    if ($nid) {
      $flagAr->deleteNodeFlag($nid);
    }
    else if($cid) {
      $flagAr->deleteCommentFlag($cid);
    }
    
    $this->responseJSON(array(), "success");
  }
  
  public function actionGetFlaggedNodes() {
    //TODO:: 权限检查
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      //$this->responseError("http error");
    }
    
    $flagAr = new FlagAR();
    
    $query = new CDbCriteria();
    $query->addCondition($flagAr->getTableAlias().".nid <> 0");
    $query->select = "distinct ".$flagAr->getTableAlias().".nid AS distinct_nid" .$flagAr->getTableAlias().".*";
    $query->with = array("node", "comment");
    
    $flags = $flagAr->findAll($query);
    
    $retdata = array();
    foreach ($flags as $flag) {
      $data = $flag->attributes;
      if ($flag->node)
        $data["node"] = $flag->node->attributes;
      if ($flag->comment)
        $data["comment"] = $flag->comment->attributes;
      
      $retdata[] = $data;
    }
    
    $this->responseJSON($retdata, "success");
  }
  
  public function actionGetFlaggedComments() {
    //TODO:: 权限检查
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      //$this->responseError("http error");
    }
    
    $flagAr = new FlagAR();
    
    $query = new CDbCriteria();
    $query->addCondition($flagAr->getTableAlias().".cid <> 0");
    $query->select = "distinct ".$flagAr->getTableAlias().".cid AS distinct_cid" .$flagAr->getTableAlias().".*";
    $query->with = array("node", "comment");
    
    $flags = $flagAr->findAll($query);
    
    $retdata = array();
    foreach ($flags as $flag) {
      $data = $flag->attributes;
      if ($flag->node)
        $data["node"] = $flag->node->attributes;
      if ($flag->comment)
        $data["comment"] = $flag->comment->attributes;
      
      $retdata[] = $data;
    }
    
    $this->responseJSON($retdata, "success");
  }
}

