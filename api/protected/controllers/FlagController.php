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
    
    // 如果有数据但是不是数字
    // 说明参数错误
    if ($nid && !is_numeric($nid)) {
      $this->responseError("invalid params");
    }
    if ($cid && !is_numeric($cid)) {
      $this->responseError("invalid params");
    }
    
    $uid = Yii::app()->user->getId();
    
    $flagAr = new FlagAR();
    $flagAr->uid = $uid;
    if ($nid) {
      $flagAr->nid = $nid;
    }
    if ($cid) {
      $flagAr->cid = $cid;
    }
    
    // 检查之前是否flag过
    if ($flagAr->nid) {
      $flagold = FlagAR::model()->findByAttributes(array("uid" => $flagAr->uid, "nid" => $flagAr->nid));
    }
    else {
      $flagold = FlagAR::model()->findByAttributes(array("uid" => $flagAr->uid, "cid" => $flagAr->cid));
    }
    
    if ($flagold) {
      $this->responseError('flagged');
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
    
    $node = NodeAR::model()->findByPk($nid);

    if (!$nid && !$cid) {
      return $this->responseError("invalid params");
    }
    
    $flagAr = new FlagAR();
    if ($nid) {
      if (!Yii::app()->user->checkAccess("removeFlag", array("country_id" => $node->country_id))) {
        return $this->responseError("permission deny");
      }
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
    
    if (!Yii::app()->user->checkAccess("listFlagedNode")) {
      return $this->responseError("permission deny");
    }
    
    $user = UserAR::model()->findByPk(Yii::app()->user->getId());
    $flagAr = new FlagAR();
    
    $query = new CDbCriteria();
    $query->addCondition($flagAr->getTableAlias().".nid <> 0");
    $query->select = "distinct ".$flagAr->getTableAlias().".nid AS distinct_nid" .$flagAr->getTableAlias().".*";
    $query->with = array("node");
    
    $flags = $flagAr->findAll($query);
    
    $allnodes = array();
    foreach ($flags as $flag) {
      $data = $flag->attributes;
      if ($flag->node) {
        // TODO:: 是否有必要返回 flag 的次数， country数据 和 user 数据 ?
        $allnodes[$flag->node->nid] = $flag->node->attributes;
      }
    }
    
    // 只处理 node
    $retnodes = array();
    if (Yii::app()->user->role == UserAR::ROLE_COUNTRY_MANAGER) {
      foreach ($allnodes as $node) {
        if ($node['country_id'] == Yii::app()->user->country_id) {
          $retnodes[] = $node;
        }
      }
    }
    else  {
      $retnodes = $allnodes;
    }
    
    $this->responseJSON($retnodes, "success");
  }
  
  public function actionGetFlaggedComments() {
    //TODO:: 权限检查
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      //$this->responseError("http error");
    }
    
    if (!Yii::app()->user->checkAccess("listFlagedComment")) {
      return $this->responseError("permission deny");
    }
    
    $flagAr = new FlagAR();
    
    $query = new CDbCriteria();
    $query->addCondition($flagAr->getTableAlias().".cid <> 0");
    $query->select = "distinct ".$flagAr->getTableAlias().".cid AS distinct_cid" .$flagAr->getTableAlias().".*";
    $query->with = array("node", "comment");
    
    $flags = $flagAr->findAll($query);
    
    $cids = array();
    foreach ($flags as $flag) {
      if ($flag->comment) {
        $cids[] = $flag->comment->cid;
      }
    }
    
    // 查询出comment 后 还需要查询对应的node, 因为我们返回当前的国家管理员下面的node 的 comment
    $query = new CDbCriteria();
    $query->addInCondition("cid", $cids);
    $query->with = array("node");
    
    $comments = CommentAR::model()->findAll($query);
    
    $retcomments = array();
    if (Yii::app()->user->role == UserAR::ROLE_COUNTRY_MANAGER) {
      foreach ($comments as $comment) {
        $node = $comment->node;
        if ($node->country_id == Yii::app()->user->country_id) {
          $retcomments[] = $comment->attributes;
        }
      }
    }
    else {
      foreach ($comments as $comment) {
        $retcomments[] = $comment->attributes;
      }
    }
    
    $this->responseJSON($retcomments, "success");
  }
}

