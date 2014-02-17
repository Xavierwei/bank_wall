<?php

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
			$type = htmlspecialchars($request->getPost("type"));
			$isIframe = htmlspecialchars($request->getPost("iframe"));
      $isFlash = htmlspecialchars($request->getPost("flash"));

			$nodeAr = new NodeAR();
			if($isIframe || $isFlash) {
				$fileUpload = CUploadedFile::getInstanceByName("file");
				$validateUpload = $nodeAr->validateUpload($fileUpload, $type);
				if($validateUpload !== true) {
					if($isIframe){
						$this->render('post', array(
							'code'=>$validateUpload
						));
					}
					else {
						$this->responseError($validateUpload);
					}
				}
			}
      $nodeAr->description = htmlspecialchars($request->getPost("description"));
      $nodeAr->type = $type;
			if($isIframe || $isFlash) {
				$nodeAr->file = $nodeAr->saveUploadedFile($fileUpload);
			}
			else {
				$file = $request->getPost("file");
				if(!file_exists(ROOT.$file)) {
					exit();
				}
				$_x = $request->getPost("x");
				if($_x && $type == 'photo') {
					$_y = $request->getPost("y");
					$_width = $request->getPost("width");
					$_height = $request->getPost("height");
					$nodeAr->file = $nodeAr->cropPhoto($file, $_x, $_y, $_width);
				}
        else {
          $nodeAr->file = $file;
        }
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

				if($isIframe){
					$this->render('post', array(
						'code'=>1
					));
				} else {
					$this->responseJSON($retdata, "success");
				}
      }
      else {
        $this->responseError($nodeAr->getErrors());
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
          $this->responseError("invalid params ( not found node)");
      }
      
      // 权限检查
      if(!Yii::app()->user->checkAccess("deleteOwnNode", array("uid" => $nodeAr->uid))) {
        if(!Yii::app()->user->checkAccess("deleteAnyNode", array("country_id" => $nodeAr->country_id))) {
          return $this->responseError("permission deny1");
        }
        else {
          return $this->responseError("permission deny2");
        }
      }
      
      $nodeAr->deleteByPk($nodeAr->nid);
			$nodeAr->deleteRelatedData($nodeAr->nid);
			LikeAR::model()->saveTopOfDay($nodeAr);
			LikeAR::model()->saveTopOfMonth($nodeAr);
      
      return $this->responseJSON($nodeAr->attributes, "success");
  }

	public function actionGetPageByNid(){
		$request = Yii::app()->getRequest();
		$nid = $request->getParam("nid");
		$pagenum = $request->getParam("pagenum");
		$page = NodeAR::model()->getPageByNid($nid, $pagenum);
		$this->responseJSON($page, "success");
	}

  public function actionList() {
      // TODO:: Order by like / Search by hashtag / Search by keyword
      $request = Yii::app()->getRequest();
      
      $type = $request->getParam("type");
      $country_id = $request->getParam("country_id");
      $uid = $request->getParam("uid");
      $showall = $request->getParam("showall");
			$mycomment = $request->getParam("mycomment");
			$mylike = $request->getParam("mylike");
			$topday = $request->getParam("topday");
			$topmonth = $request->getParam("topmonth");
      
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
					if($mycomment) { // filter my commented contents
						$query->addCondition("comment.uid=:uid", "AND");
						$params[":uid"] = $uid;
					}
					else if($mylike) { // filter my liked contents
						$query->addCondition("like.uid=:uid", "AND");
						$params[":uid"] = $uid;
					}
					else {  // filter my posted contents
						$query->addCondition($nodeAr->getTableAlias().".uid=:uid", "AND");
						$params[":uid"] = $uid;
					}
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
      $query->select = "*". ", count(like_id) AS likecount". ",topday_id AS topday" . ",topmonth_id AS topmonth";
      $query->join = 'left join `like` '.' on '. '`like`' .".nid = ". $nodeAr->getTableAlias().".nid";
			$query->join .= ' left join `topday` '.' on '. '`topday`' .".nid = ". $nodeAr->getTableAlias().".nid";
			$query->join .= ' left join `topmonth` '.' on '. '`topmonth`' .".nid = ". $nodeAr->getTableAlias().".nid";
      $query->group = $nodeAr->getTableAlias().".nid";

      // 本日最佳
			if($topday) {
				$query->select = "*". ",topday_id AS topday";
				$query->join = 'right join `topday` '.' on '. '`topday`' .".nid = ". $nodeAr->getTableAlias().".nid";
			}

			// 本月最佳
			if($topmonth) {
				$query->select = "*". ",topmonth_id AS topmonth";
				$query->join = 'right join `topmonth` '.' on '. '`topmonth`' .".nid = ". $nodeAr->getTableAlias().".nid";
			}

      // 我评论过的的内容
			if($mycomment) {
				$query->select = "*";
				$query->join = 'right join `comment` on `comment`.nid = '.$nodeAr->getTableAlias().'.nid';
			}

      // 我喜欢过的内容
			if($mylike) {
				$query->select = "*";
				$query->join = 'right join `like` on `like`.nid = '.$nodeAr->getTableAlias().'.nid';
			}

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
					$page = 1;
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
          $data["description"] = htmlentities($node->description);
          $data["likecount"] = $node->likecount;
          $data["commentcount"] = $commentAr->totalCommentsByNode($node->nid);
          $data["user"] = $node->user ? $node->user->attributes : array();
          $data["country"] = $node->country ? $node->country->attributes: array();
          $data["user_liked"] = $node->user_liked;
          $data["user_flagged"] = $node->user_flagged;
          if($uid && isset($node->user['uid']) && Yii::app()->user->getId() == $node->user['uid']) {
            $data["mynode"] = TRUE;
          }
          //$data["like"] = $node->like;
          if($node->topday) {
            $data["topday"] = TRUE;
          }
          if($node->topmonth) {
            $data["topmonth"] = TRUE;
          }
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
          $order .= " ".$nodeAr->getTableAlias().".datetime DESC";
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
    
    $this->responseJSON(array( "left" => $rightRet,"right" => $leftRet, "node" => $nodedata), "success");
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


  
  public function actionPostByMail() {
		try {
			$request = Yii::app()->getRequest();
			if (!$request->isPostRequest) {
				return $this->responseJSON(null, null, false);
			}
			$userEmail = $request->getPost("user");
			$desc = $request->getPost("desc");
			$user = UserAR::model()->findByAttributes(array("company_email" => $userEmail));
			if (!$user) {
				$user = UserAR::model()->findByAttributes(array("personal_email" => $userEmail));
			}
      if (!$desc) {
        $ret = 'Please write the subject use for description';
        return $this->responseJSON(null, $ret, false);
      }
			if (!$user) {
				$ret = 'Debug Message (To be delete when live): your account not in our database'; //TODO: delete when live
				return $this->responseJSON(null, $ret, false); //if the user not in our database then return nothing
			}
			$begin = 'Dear '.$user->firstname.' '.$user->lastname.',\n\n';
			$end = '\n\nSG WALL Team';
			if(empty($desc)) {
				$ret = $begin.'Please write the email subject.'.$end;
				return $this->responseJSON(null, $ret, false);
			}
			$uploadFile = CUploadedFile::getInstanceByName("photo");
			if (!$uploadFile) {
				$ret = $begin.'Please attach photo or video in attachment.'.$end;
				return $this->responseJSON(null, $ret, false);
			}
			else {
			}
			if ($uploadFile) {
				//$mime = $uploadFile->getType();
				exec("/usr/bin/file -b --mime {$uploadFile->tempName}", $output, $status);
				$mimeArray = explode(';',$output[0]);
				$mime = $mimeArray[0];
				$size = $uploadFile->getSize();
				$allowPhotoMime = array(
					"image/gif", "image/png", "image/jpeg", "image/jpg"
				);
				$allowVideoMime = array(
					"video/mov","video/quicktime", "video/x-msvideo", "video/x-ms-wmv", "video/wmv", "video/mp4", "video/avi", "video/3gp", "video/3gpp", "video/mpeg", "video/mpg", "application/octet-stream", "video/x-ms-asf"
				);
				if (in_array($mime, $allowPhotoMime)) {
					$type = 'photo';
					if($size > 5 * 1024000) {
						$ret = $begin.'The size of your image file should not exceed 5MB'.$end;
						return $this->responseJSON(false, $ret, false);
					}
					list($w, $h) = getimagesize($uploadFile->tempName);
					if($w < 450 || $h < 450) {
						$ret = $begin.'For optimal resolution, please use a format of at least 450x450 px'.$end;
						return $this->responseJSON(false, $ret, false);
					}
				}
				else if (in_array($mime, $allowVideoMime)) {
					$type = 'video';
					if($size > 7 * 1024000) {
						$ret = $begin.'The size of your image file should not exceed 7MB'.$end;
						return $this->responseJSON(false, $ret, false);
					}
				}
				else {
					$ret = $begin.'The photo only support gif, png, jpeg, jpg\nThe video only support mov, wmv, mp4, avi, 3pg'.$end;
					return $this->responseJSON(false, $ret, false);
				}
			}
			$node = new NodeAR();
			$node->uid = $user->uid;
			$node->country_id = $user->country_id;
			$node->type = $type;
			$node->status = 1; // The default status is blocked when the content from email
			$node->file = $node->saveUploadedFile($uploadFile);
			$node->description = htmlspecialchars($desc);

			if ($node->validate()) {
				$success = $node->save();
				if (!$success) {
					return $this->responseJSON(false, null, false);
				}
			}
			else {
				return $this->responseJSON(false, null, false);
			}

			//success
			$ret = $begin.'Your '.$type.' is success submit, after approved, you can visit the '.$type.' via this url:\nhttp://64.207.184.106/sgwall/#/nid/'.$node->nid.$end;
			return $this->responseJSON(true, $ret, false);
		}
		catch (Exception $e) {
			return $this->responseJSON(false, $e->getMessage(), false);
		}
  }
}

