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
    
    //先用默认的用户来模拟登陆问题
    $uid = UserAR::model()->find()->uid;
    $user = UserAR::model()->findByPk($uid);
    
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
      // TODO::
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
      
      $nodeAr->delete();
      
      return $this->responseJSON($nodeAr->attributes, "success");
  }
  
  public function actionList() {
      // TODO:: Order by like / Search by hashtag / Search by keyword
      $request = Yii::app()->getRequest();
      
      $type = $request->getParam("type");
      $country_id = $request->getParam("country_id");
      $uid = $request->getParam("uid");
      
      // 3个参数必须填一个
      if (!$type && !$country_id && !$uid) {
          return $this->responseError("http error");
      }
      
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
      // 暂时不支持like
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
          $query->addCondition("contry_id = :country_id", "AND");
          $params[":country_id"] = $country_id;
      }
      if ($uid) {
          $query->addCondition("uid=:uid", "AND");
          $params[":uid"] = $uid;
      }
      
      if ($start) {
          $start = strtotime($start);
          $params[":start"] = $start;
          $query->addCondition("datetime >= :start", "AND");
      }
      if ($end) {
          $end = strtotime($end);
          $params[":end"] = $end;
          $query->addCondition("datetime<= :end", "AND");
      }
      
      // 需要验证用户权限
      $user = UserAR::model()->findByPk(Yii::app()->getId());
      if ($user && $user->role == UserAR::ROLE_ADMIN) {
          $query->addCondition("status =:status", "AND");
          $params[":status"] = $status;
      }
      // 否则 status 只是是 published 状态
      else {
          $status = NodeAR::PUBLICHSED;
          $query->addCondition($nodeAr->getTableAlias().".status = :status", "AND");
          $params[":status"] = $status;
      }
      
      $order = "ORDER BY ";
      if ($orderby == "datetime") {
          $order .= " datetime DESC";
      }
      else if ($orderby == "like") {
        // orderby like 比较复杂， 需要用到join 和 group
        // 还需要增加一个额外的 SELECT 
        $likeAr = new LikeAR();
        $query->select = "*". ", count(like.nid) AS likecount";
        $query->join = 'left join `like` '.' on '.$likeAr->getTableAlias() .".nid = ". $nodeAr->getTableAlias().".nid";
        $query->group ="`like`.nid";
        $order .= "`like` DESC";
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
        $query->addSearchCondition("hashtag", '#'.$hashtag);
      }
      
      $res = NodeAR::model()->with("user", "country")->findAll($query);
      $retdata = array();
      foreach ($res as $node) {
          $data = $node->attributes;
          $data["likecount"] = $node->likecount;
          $data["user"] = $node->user->attributes;
          $data["country"] = $node->country->attributes;
          $retdata[] = $data;
      }
      
      $this->responseJSON($retdata, "success");
  }
}

