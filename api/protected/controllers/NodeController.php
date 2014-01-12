<?php

/**
 * @author jackey <jziwenchen@gmail.com>
 */
class NodeController extends Controller {
  public function actionTest() {
    $nodeAr = new NodeAR();
    $nodeAr->description = "hi, #hashtag and #China in #shanghai";
    
    $hashtags = $nodeAr->getHashTagFromText();
    print_r($hashtags);
    $this->responseError("ERROR");
  }
  
  public function actionPost() {
    $uid = Yii::app()->user->getId();
    $user = UserAR::model()->findByPk($uid);
    
    if (!Yii::app()->user->checkAccess("addNode")) {
      return $this->responseError("permission deny");
    }
    
    if ($user) {
      $country_id = $user->country_id;
      
      $request = Yii::app()->getRequest();
      if (!$request->isPostRequest) {
        $this->responseError("http error");
      }
      
      $photoUpload = CUploadedFile::getInstanceByName("photo");
      $videoUpload = CUploadedFile::getInstanceByName("video");
      
      if ($photoUpload) {
        $type = "photo";
      }
      else if ($videoUpload){
        $type = "video";
      }
      else {
        $this->responseError("video or photo is mandatory");
      }
      
      if ($photoUpload) {
        $mime = $photoUpload->getType();
        // TODO:: 大小限制
        $size = $photoUpload->getSize(); //in bytes
        $allowMime = array(
            "image/gif", "image/png", "image/jpeg", "image/jpg"
        );
        if (!in_array($mime, $allowMime)) {
          $this->responseError("photo's media type is not allowed");
        }
      }
      
      if ($videoUpload) {
        // TODO:: 暂时判断不出视频类型，需要更多测试实例
      }
      
      $nodeAr = new NodeAR();
      $nodeAr->description = $request->getPost("description");
      $nodeAr->type = $type;
      if ($type == "photo") {
        $nodeAr->file = $nodeAr->saveUploadedFile($photoUpload);
      }
      else {
        $nodeAr->file = $nodeAr->saveUploadedFile($videoUpload);
      }
      $nodeAr->uid = $uid;
      $nodeAr->country_id = $country_id;
      
      if ($nodeAr->validate()) {
        $success = $nodeAr->save();
        if (!$success) {
            $this->responseError("exception happended");
        }
        $retdata = $nodeAr->attributes;
        $retdata['user'] = $nodeAr->user->attributes;
        $retdata['country'] = $nodeAr->country->attributes;
        
        $this->responseJSON($retdata, "success");
      }
      else {
        $this->responseError(current(array_shift($nodeAr->getErrors())));
      }
    }
    else {
      $this->responseError("unknown error");
    }
  }
  
  public function actionPut() {
      $request = Yii::app()->getRequest();
      if (!$request->isPostRequest) {
        $this->responseError("http error");
      }
      
      $nid = $request->getPost("nid");
      if (!$nid) {
        $this->responseError("invalid params");
      }
      
      $node = NodeAR::model()->findByPk($nid);
      
      if ($node) {
        $photoUpload = CUploadedFile::getInstanceByName("photo");
        $videoUpload = CUploadedFile::getInstanceByName("video");
        if ($photoUpload) {
          $type = "photo";
        }
        else if ($videoUpload){
          $type = "video";
        }
        // 在这里和添加有点区别，我们不强制用户传 Media 过来
        else {
          $type = FALSE;
        }
        
        // 在这里做权限检查
        // 如果用户在更改 media, 就要检查更改 media 的权限
        if ($type && !Yii::app()->user->checkAccess("updateNodeMedia", array("country_id" => $node->country_id))) {
          return $this->responseError("permission deny");
        }
        // 如果做内容修改， 用户就应该有修改自己内容的权限
        else if (!Yii::app()->user->checkAccess("updateOwnNode", array("uid" => $node->uid))) {
          return $this->responseError("permission deny");
        }

        if ($photoUpload) {
          $mime = $photoUpload->getType();
          $allowMime = array(
              "image/gif", "image/png", "image/jpeg", "image/jpg"
          );
          if (!in_array($mime, $allowMime)) {
            $this->responseError("photo's media type is not allowed");
          }
        }

        if ($videoUpload) {
          // TODO:: 暂时判断不出视频类型，需要更多测试实例
        }
        
        // 修改 description
        $description = $request->getPost("description");
        if ($description) {
          $node->description =  $description;
        }
        
        $status = $request->getPost("status");
        if ($status) {
          // TODO:: 这里修改node 状态需要权限检查， 暂时没有实现权限检查
          $node->status = $status;
        }
        
        // 修改media
        if ($type == "photo") {
          $node->file = $node->saveUploadedFile($photoUpload);
          $node->type = $type;
        }
        elseif($type == "video") {
          $node->file = $node->saveUploadedFile($videoUpload);
          $node->type = $type;
        }
        if ($node->validate()) {
          $node->beforeSave();
          $ret = $node->updateByPk($node->nid, $node->attributes);
          $this->responseJSON($node->attributes, "success");
        }
        else {
          $this->responseError(current(array_shift($node->getErrors())));
        }
      }
      else {
        $this->responseError("node not found");
      }
  }
  
  public function actionDelete() {
      $request = Yii::app()->getRequest();
      
      if (!$request->isPostRequest) {
          $this->responseError("http error");
      }
      
      $nid = $request->getPost("nid");
      
      if (!$nid) {
          $this->responseError("invalid params");
      }
      
      $nodeAr = NodeAR::model()->findByPk($nid);
      if(!$nodeAr) {
          $this->responseError("invalid params");
      }
      
      // 权限检查
      if (!Yii::app()->user->checkAccess("deleteAnyNode", array("country_id" => $nodeAr->country_id))) {
        return $this->responseError("permission deny");
      }
      
      $nodeAr->deleteByPk($nodeAr->nid);
      
      return $this->responseJSON($nodeAr->attributes, "success");
  }
  
  public function actionList() {
      // TODO:: Order by like / Search by hashtag / Search by keyword
      $request = Yii::app()->getRequest();
      
      $type = $request->getParam("type");
      $country_id = $request->getParam("country_id");
      $uid = $request->getParam("uid");
      $showall = $request->getParam("showall");
      
      // 3个参数必须填一个
//      if (!$type && !$country_id && !$uid) {
//          return $this->responseError("http error");
//      }
      
      $page = $request->getParam("page");
      if (!$page) {
          $page = 1;
      }
      $pagenum = $request->getParam("pagenum");
      if (!$pagenum) {
          $pagenum = 10;
      }
      
      // 开始时间和结束时间
      $start = $request->getParam("start");
      $end = $request->getParam("end");
      
      // orderby 可选参数:
      // [datetime, like]
      $orderby = $request->getParam("orderby");
      
      // 需要验证是否是管理员
      $status = $request->getParam("status");
      
      // 配置查询条件
      $query = new CDbCriteria();
      $nodeAr = new NodeAR();
      $params = &$query->params;
      if ($type) {
          $query->addCondition("type=:type", "AND");
          $params[":type"] = $type;
      }
      if ($country_id) {
          $query->addCondition("country.country_id = :country_id", "AND");
          $params[":country_id"] = $country_id;
      }
      if ($uid) {
          $query->addCondition("uid=:uid", "AND");
          $params[":uid"] = $uid;
      }
      
      if ($start) {
          $start = strtotime($start);
          $params[":start"] = $start;
          $query->addCondition($nodeAr->getTableAlias().".datetime >= :start", "AND");
      }
      if ($end) {
          $end = strtotime($end);
          $params[":end"] = $end;
          $query->addCondition($nodeAr->getTableAlias().".datetime<= :end", "AND");
      }
      
      // 需要验证用户权限
      $user = UserAR::model()->findByPk(Yii::app()->getId());
      if ($user && ($user->role == UserAR::ROLE_ADMIN || $user->role == UserAR::ROLE_COUNTRY_MANAGER) && $showall) {
          // 如果是管理员，我们就忽略掉status 参数，这样子他们就可以看到所有的node
        if ($user->role == UserAR::ROLE_ADMIN) {
          // admin 就不必要 增加status 参数了
        }
        else if ($user->role == UserAR::ROLE_COUNTRY_MANAGER) {
          // 这里要增加个条件
          // country manager 只允许看到自己国家的block掉的 node
          $query->addCondition("country_id = :country_id");
          $query->params[':country_id'] = $user->country_id;
        }
      }
      // 否则 status 只能是 published 状态
      else {
          $status = NodeAR::PUBLICHSED;
          $query->addCondition($nodeAr->getTableAlias().".status = :status", "AND");
          $params[":status"] = $status;
      }
      // like count
      $likeAr = new LikeAR();
      $query->select = "*". ", count(like_id) AS likecount";
      $query->join = 'left join `like` '.' on '. '`like`' .".nid = ". $nodeAr->getTableAlias().".nid";
      $query->group = $nodeAr->getTableAlias().".nid";
      
      $order = "";
      if ($orderby == "datetime") {
          $order .= " ".$nodeAr->getTableAlias().".datetime DESC";
          $query->order = $order;
      }
      else if ($orderby == "like") {
        // orderby like 比较复杂， 需要用到join 和 group
        // 还需要增加一个额外的 SELECT 
        $order .= "`likecount` DESC";
        $query->order = $order;
      }
      else if ($orderby == "random") {
          // 随机查询需要特别处理
          // 如下， 首先随机出 $pagenum 个数的随机数，大小范围在 max(nid), min(nid) 之间
          // 再用 nid in (随机数) 去查询
          $sql = "SELECT max(nid) as max, min(nid) as min FROM node";
          $ret = Yii::app()->db->createCommand($sql);
          $row = $ret->queryRow();
          $nids = array();
          $max_run = 0;
          while (count($nids) < $pagenum && $max_run < $pagenum * 10) {
              $max_run ++;
              $nid = mt_rand($row["min"], $row["max"]);
              if (!isset($nids[$nid])) {
                  $cond = array();
                  foreach ($params as $k => $v) {
                      $cond[str_replace(":", "", $k)] = $v;
                  }
                  $node = NodeAR::model()->findByPk($nid);
                  if (!$node) {
                    continue;
                  }
                  $isNotWeWant = FALSE;
                  foreach ($cond as $k => $v) {
                      if($node->{$k} != $v) {
                          $isNotWeWant = TRUE;
                          break;
                      }
                  }
                  if ($isNotWeWant) {
                          continue;
                  }
                  $nids[$nid] = $nid;
              }
          }
          $query->addInCondition($nodeAr->getTableAlias().".nid", $nids, "AND");
      }
      
      $query->limit = $pagenum;
      $query->offset = ($page - 1 ) * $pagenum;
      $query->with = array("user", "country");
      
      //TODO:: 搜索功能 现在是全文搜索，如果效果不好 可能改为分词搜索 (需要更多查询表)
      // 集成 keyword 查询, 查询 description 中的关键字
      $keyword = $request->getParam("keyword");
      if ($keyword) {
        $query->addSearchCondition("description", $keyword);
      }
      
      // 集成 hashtag 搜索, 查询 hashtag 中的关键字
      $hashtag = $request->getParam("hashtag");
      if ($hashtag) {
        $query->addSearchCondition("hashtag", $hashtag);
      }
      
      $res = NodeAR::model()->with("user", "country")->findAll($query);

      $retdata = array();
      $commentAr = new CommentAR();
      foreach ($res as $node) {
          $data = $node->attributes;
          $data["likecount"] = $node->likecount;
          $data["commentcount"] = $commentAr->totalCommentsByNode($node->nid);
          $data["user"] = $node->user ? $node->user->attributes : array();
          $data["country"] = $node->country ? $node->country->attributes: array();
          $data["user_liked"] = $node->user_liked;
          $data["like"] = $node->like;
          $retdata[] = $data;
      }
      
      $this->responseJSON($retdata, "success");
  }
  
  // 返回某个  nid 的 前10条和后10条
  // 这个里支持的参数是
  // @param type
  // @param country_id
  // @param uid 
  // @param orderby  
  // @param nid
  public function actionGetNeighbor() {
    $request = Yii::app()->getRequest();
    
    $type = $request->getParam("type");
    $country_id = $request->getParam("country_id");
    $uid = $request->getParam("uid");
    $nid = $request->getParam("nid");
    $orderby = $request->getParam("orderby");
    
    $nodeAr = new NodeAR();
    
    if (!$nid) {
      return $this->responseError("invalid params");
    }
    
    // 构造查询条件
    $query = new CDbCriteria();
    if ($type) {
      $query->addCondition("type = :type");
      $query->params[":type"] = $type;
    }
    
    if ($country_id) {
      $query->addCondition("country_id = :country_id");
      $query->params[":country_id"] = $country_id;
    }
    
    if ($uid) {
      $query->addCondition("uid = :uid");
      $query->params[":uid"] = $uid;
    }
    
    if ($orderby) {
      $order = " ";
      if ($orderby == "datetime") {
          $order .= " datetime DESC";
          $query->order = $order;
      }
      else if ($orderby == "like") {
        // orderby like 比较复杂， 需要用到join 和 group
        // 还需要增加一个额外的 SELECT 
        $likeAr = new LikeAR();
        $query->select = "*". ", count(like.nid) AS likecount";
        $query->join = 'left join `like` '.' on '.$likeAr->getTableAlias() .".nid = ". $nodeAr->getTableAlias().".nid";
        $query->group ="`like`.nid";
        $order .= "`likecount` DESC";
        
        $query->order = $order;
      }
      else if ($orderby == "random") {
          // 随机查询需要特别处理
          // 如下， 首先随机出 $pagenum 个数的随机数，大小范围在 max(nid), min(nid) 之间
          // 再用 nid in (随机数) 去查询
          $sql = "SELECT max(nid) as max, min(nid) as min FROM node";
          $ret = Yii::app()->db->createCommand($sql);
          $row = $ret->queryRow();
          $nids = array();
          $max_run = 0;
          while (count($nids) < $pagenum && $max_run < $pagenum * 10) {
              $max_run ++;
              $nid = mt_rand($row["min"], $row["max"]);
              if (!isset($nids[$nid])) {
                  $cond = array();
                  foreach ($params as $k => $v) {
                      $cond[str_replace(":", "", $k)] = $v;
                  }
                  $node = NodeAR::model()->findByPk($nid);
                  if (!$node) {
                    continue;
                  }
                  $isNotWeWant = FALSE;
                  foreach ($cond as $k => $v) {
                      if($node->{$k} != $v) {
                          $isNotWeWant = TRUE;
                          break;
                      }
                  }
                  if ($isNotWeWant) {
                          continue;
                  }
                  $nids[$nid] = $nid;
              }
          }
          $query->addInCondition("nid", $nids, "AND");
      }
    }
    
    $query->addCondition($nodeAr->getTableAlias().".status = :status", "AND");
    $query->params[":status"] =  NodeAR::PUBLICHSED;
    
    $query->limit = 10;

    $query->with = array("user", "country");
    
    // 在这里 要查询后当前 nid 的前10条和后10条, 要查询2次
    // 前10条
    $query1 = clone $query;
    $query1->addCondition($nodeAr->getTableAlias(). '.nid < :nid');
    $query1->params[":nid"] = $nid;
    
    
    // 后10条
    $query2 = clone $query;
    $query2->addCondition($nodeAr->getTableAlias().'.nid > :nid');
    $query2->params[":nid"] = $nid;
    
    
    // 构造完后 查询结果
    $leftRet = array();
    $res = NodeAR::model()->findAll($query1);
    foreach ($res as $node) {
        $data = $node->attributes;
        $data["likecount"] = $node->likecount;
        $data["user"] = $node->user ? $node->user->attributes : array();
        $data["country"] = $node->country ? $node->country->attributes: array();
        $data["user_liked"] = $node->user_liked;
        $data["like"] = $node->like;
        $leftRet[] = $data;
    }
    
    $rightRet = array();
    $res = NodeAR::model()->findAll($query2);
    foreach ($res as $node) {
        $data = $node->attributes;
        $data["likecount"] = $node->likecount;
        $data["user"] = $node->user ? $node->user->attributes : array();
        $data["country"] = $node->country ? $node->country->attributes: array();
        $data["user_liked"] = $node->user_liked;
        $data["like"] = $node->like;
        $rightRet[] = $data;
    }
    
    // current nid
    $node = NodeAR::model()->with(array("user", "country"))->findByPk($nid);
    $nodedata = $node->attributes;
    $nodedata["country"] = $node->country? $node->country->attributes : array();
    $nodedata["user"] = $node->user? $node->user->attributes : array();
    
    $this->responseJSON(array("left" => $leftRet, "right" => $rightRet, "node" => $nodedata), "success");
  }
  
  public function actionGetbyid() {
    $request = Yii::app()->getRequest();
    $nid = $request->getParam("nid");
    
    if (!$nid) {
      return $this->responseError("invalid params");
    }
    
    $node = NodeAR::model()->with(array("user", "country"))->findByPk($nid);
    
    $user = UserAR::model()->findByPk(Yii::app()->user->getId());
    // 要察看unpublish 和 blocked 的node 需要权限
    if ($node->status == NodeAR::UNPUBLISHED || $node->status == NodeAR::BLOCKED) {
      if (!$user) {
        return $this->responseError("permission deny");
      }
      if (($user->role == UserAR::ROLE_COUNTRY_MANAGER && $user->country_id == $node->country_id) || $user->role == UserAR::ROLE_ADMIN) {
        //nothing todo
      }
      else {
        return $this->responseError("permission deny");
      }
    }
    
    $retdata = $node->attributes;
    $retdata["country"] = $node->country ? $node->country->attributes : array();
    $user = $node->user->attributes;
    $retdata["user"] = $node->user ? $node->user->getOutputRecordInArray($user): array();


    $this->responseJSON($retdata, "success");
  }
}

