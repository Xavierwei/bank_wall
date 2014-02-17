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

    if(Yii::app()->user->isGuest) {
      return $this->responseError(601);
    }

    $uid = Yii::app()->user->getId();
    if (Yii::app()->user->checkAccess("postComment")) {
      return $this->responseError(602);
    }
    
    $content = htmlspecialchars($request->getPost("content"));

		if(empty($content)) {
			return $this->responseError(701);
		}

		if(strlen($content) > 140) {
			return $this->responseError(702);
		}
    
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
  
  public function actionPut() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $cid = $request->getPost("cid");
    if (!$cid || !is_numeric($cid)) {
      $this->responseError("invalid params");
    }
    
    $comment = CommentAR::model()->with("node")->findByPk($cid);
    if (!$comment) {
      $this->responseError("comment not found");
    }
    
    // 权限检查
    $node = $comment->node;
    if (!Yii::app()->user->checkAccess("updateAnyComment", array("country_id" => $node->country_id))  
            && !Yii::app()->user->checkAccess("updateOwnComment", array("uid" => $comment->uid))) {
      return $this->responseError("permission deny");
    }
    
    // 参数应该和 comment POST 不一样， 只允许 content 数据
    $content = $request->getPost("content");
    if (!$content) {
      $this->responseError("invalid params");
    }
    $comment->content = $content;
    if ($comment->validate()) {
      $comment->updateByPk($comment->cid, array("content" => $comment->content));
      $this->responseJSON($comment->attributes, "success");
    }
    else {
      $this->responseError(current(array_shift($comment->getErrors())));
    }
  }
  
  public function actionDelete() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    $cid = $request->getPost("cid");
    if (!$cid) {
      $this->responseError("invalid params");
    }
    
    $comment = CommentAR::model()->with("node")->findByPk($cid);
    if (!$comment) {
      $this->responseJSON(array(), "success");
    }
    $node = $comment->node;
    if (!Yii::app()->user->checkAccess("deleteAnyComment", array("country_id" => $node->country_id))
            && !Yii::app()->user->checkAccess("deleteOwnComment", array("uid" => $comment->uid))) {
      return $this->responseError("permission deny");
    }
    
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
    $shownode = $request->getParam("shownode");

		$page = $request->getParam("page");
		if (!$page) {
			$page = 1;
		}
		$pagenum = $request->getParam("pagenum");
		if (!$pagenum) {
			$pagenum = 10;
		}
    
    //$orderby = $request->getParam("orderby");
    //if (!$orderby) {
      $orderby = "datetime";
    //}
    
    $order = $request->getParam("order");
    if (!$order) {
      $order = "ASC";
    }
    if (strtoupper($order) != "DESC" && strtoupper($order) != "ASC") {
      $order = "ASC";
    }
    
    $query = new CDbCriteria();
    if ($nid) {
      $query->addCondition("nid=:nid");
      $query->params[":nid"] = $nid;
    }
		$query->limit = $pagenum;
		$query->offset = ($page - 1 ) * $pagenum;
    $query->order = CommentAR::model()->getTableAlias().".$orderby $order";
    
    $query->with = array("user");
    
    $comments = CommentAR::model()->findAll($query);
    
    $retdata = array();
    
    foreach ($comments as $comment) {
      $commentdata = $comment->attributes;
      $commentdata['content'] = htmlentities($commentdata['content']);
      // 加载 评论相关的用户资料
      $country = $comment->user? $comment->user->country: NULL;
      $user = $comment->user? $comment->user->attributes: NULL;
      $user["country"] = $country ? $country->attributes: NULL;
      $commentdata["user"] = $comment->user? $comment->user->getOutputRecordInArray($user): NULL;
      if ($shownode) {
        $commentdata['node'] = NodeAR::model()->findByPk($comment->attributes['nid']);
      }
			if(isset($user['uid']) && Yii::app()->user->getId() == $user['uid']) {
				$commentdata["mycomment"] = TRUE;
			}
      $retdata[] = $commentdata;
    }

//    $retdata = array(
//        "node" => $node,
//        "comments" => $retdata,
//    );
    
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
  
  public function actionGetbyid() {
    $request = Yii::app()->getRequest();
    
    $cid = $request->getParam("cid");
    if (!$cid) {
      return $this->responseError("invalid params");
    }
    
    $comment = CommentAR::model()->with(array("user", "node"))->findByPk($cid);
    if (!$comment) {
      return $this->responseError("invalid params");
    }
    
    $retdata = $comment->attributes;
    if ($comment->user) {
      $user = $comment->user;
      $country = $user->country;
      $retdata["user"] = $user->getOutputRecordInArray(array("country" => $country->attributes) + $user->attributes);
    }
    if ($comment->node) {
      $retdata["node"] = $comment->node->attributes;
    }
    
    $this->responseJSON($retdata, "success");
    
  }

	public function actionFlaggedCommentsList() {
		$request = Yii::app()->getRequest();
		$nid = $request->getParam("nid");
		$retdata = CommentAR::model()->flaggedCommentsList($nid);
		$this->responseJSON($retdata, "success");
	}

}

