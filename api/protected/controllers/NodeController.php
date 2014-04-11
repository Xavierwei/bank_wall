<?php

class NodeController extends Controller {
  
  

	/**
	 * Post new node (photo/video)
	 */
	public function actionPost() {
		$uid = Yii::app()->user->getId();
		$user = UserAR::model()->findByPk($uid);
		$request = Yii::app()->getRequest();
		$isIframe = htmlspecialchars($request->getPost("iframe"));
		$isIframeRepost = htmlspecialchars($request->getPost("iframeRepost"));
		$tmp_file = $request->getPost("tmp_file");
		$is_retry = !!$tmp_file;
		$abs_tmp_file = ROOT. $tmp_file;

		if ($tmp_file && is_file(ROOT.$tmp_file)) {
			$filePath = ROOT. $tmp_file;
			$mime = NodeAR::detechFileMime($filePath);
			$size = filesize($filePath);
			$name = pathinfo($filePath, PATHINFO_BASENAME);

			$new_file_entity = array(
			  "type" => $mime,
			  "size" => $size,
			  "tmp_name" => $filePath,
			  "error" => UPLOAD_ERR_OK,
			  "name" => $name
			);
			$_FILES[pathinfo($filePath, PATHINFO_FILENAME)] = $new_file_entity;
		}
    
		if ($user) {

			if (!$request->isPostRequest) {
				$this->responseError(101);
			}
      
			$type = htmlspecialchars($request->getPost("type"));
			$isFlash = htmlspecialchars($request->getPost("flash"));

			$nodeAr = new NodeAR();
			if($isIframe || $isFlash) {
				// 在这里， 重新上传的文件名不确定 所以需要手动获得
				if ($tmp_file) {
				  $file_name = pathinfo($tmp_file, PATHINFO_FILENAME);
				}
				else {
				  $file_name = "file";
				}
				$fileUpload = CUploadedFile::getInstanceByName($file_name);
				$validateUpload = $nodeAr->validateUpload($fileUpload, $type);
        
				if($validateUpload !== true) {
					if($isIframe){
						$this->render('post', array(
							'code'=>$validateUpload
						));
						return;
					}
					else {
						return $this->responseError($validateUpload);
					}
				}
			}
			$nodeAr->description = htmlspecialchars($request->getPost("description"));
			$nodeAr->type = $type;
			if($isIframe || $isFlash ) {
				$file = $nodeAr->saveUploadedFile($fileUpload);
				if (is_array($file) && $file[0] === FALSE) {
				  $tmp_file = $file[1];
				  if (is_file($tmp_file)) {
						$base_path = ROOT;
					  	if($isIframeRepost) {
							$this->responseError(array("error" => 508, "tmp_file" => str_replace($base_path, "", $tmp_file)));
						}
					  	else {
							$this->render('post', array(
								'code'=>508,
								'tmp_file' => str_replace($base_path, "", $tmp_file),
							));
						}
					  	return;
				  }
				  else {
					$this->responseError("ffmpeg busy");
				  }
				}
				if($file) {
					$nodeAr->file = $file;
				}
				else {
					$this->render('post', array(
						'code'=>509
					));
					return;
				}
			}
			else {
				$file_id = $request->getPost("file");
				$file = NodeAR::getFile($file_id, $uid);
				if(!$file || !file_exists(ROOT.$file)) {
					exit();
				}
				$_x = $request->getPost("x");
				if($_x && $type == 'photo') {
					$_y = $request->getPost("y");
					$_width = $request->getPost("width");
					$_scale_size = $request->getPost("size");
					$nodeAr->file = $nodeAr->cropPhoto($file, $_x, $_y, $_width, $_scale_size);
				}
				else {
				  $nodeAr->file = $file;
				}
			}

			$nodeAr->uid = $uid;
			$nodeAr->country_id = $user->country_id;
			$nodeAr->status = 1; //publish by default
			if ($nodeAr->validate()) {
				$success = $nodeAr->save();

                $this->cleanAllCache();
				if (!$success) {
				    $this->responseError("exception happended");
				}
				$retdata = $nodeAr->attributes;
				$retdata['user'] = $nodeAr->user->attributes;
				$retdata['country'] = $nodeAr->country->attributes;
				
				if($isIframe && !$isIframeRepost){
					$this->render('post', array(
						'code'=>1
					));
					return;
				} else {
					if(isset($file_id)) {
						NodeAR::deleteFile($file_id);
					}
					$this->responseJSON($retdata, "success");
				}

			}
			else {
				$this->responseError($nodeAr->getErrors());
			}
		}
		else {
			if($isIframe){
				$this->render('post', array(
					'code'=>602
				));
				return;
			} else {
				return $this->responseError(602);
			}
		}
	}


	/**
	 * Update node
	 */
	public function actionPut() {
		$request = Yii::app()->getRequest();
		if (!$request->isPostRequest) {
			$this->responseError(101);
		}

		$nid = $request->getPost("nid");
		if (!$nid) {
			$this->responseError(101);
		}

		if (!Yii::app()->user->checkAccess("updateNode")) {
			return $this->responseError(602);
		}

		$node = NodeAR::model()->findByPk($nid);
      
		if ($node) {
			$description = $request->getPost("description");
			if (isset($status)) {
				$node->description =  $description;
			}

			$status = $request->getPost("status");


			if (isset($status)) {
				$node->status = $status;
				if($status == 1) {
					FlagAR::model()->deleteNodeFlag($node->nid);
				}
			}

			if ($node->validate()) {
				$node->beforeSave();

                $this->cleanAllCache();
				$ret = $node->updateByPk($node->nid, array("status" => $node->status));
				$this->responseJSON($node->attributes, "success");
			}
			else {
				$this->responseError(current(array_shift($node->getErrors())));
			}
		}
		else {
			$this->responseError(101);
		}
	}

	/*
	 * Delete content
	*/
	public function actionDelete() {

		$request = Yii::app()->getRequest();

		if (!$request->isPostRequest) {
		  $this->responseError(101);
		}

		$nid = $request->getPost("nid");

		if (!$nid) {
		  $this->responseError(101);
		}

		$nodeAr = NodeAR::model()->findByPk($nid);
		if(!$nodeAr) {
		  return $this->responseError(102);
		}

		if(!Yii::app()->user->checkAccess("deleteOwnNode", array("uid" => $nodeAr->uid))) {
			return $this->responseError(602);
		}


		$nodeAr->deleteByPk($nodeAr->nid);
		$nodeAr->deleteRelatedData($nodeAr->nid);
		// update node count in country table
		$country = CountryAR::model()->findByPk($nodeAr->country_id);
		$country->node_count =  $country->node_count - 1;
		$country->updateByPk($country->country_id, array("node_count" => $country->node_count));
		// update hashtag counts
		$hashtags =$nodeAr->attributes['hashtag'];
		foreach($hashtags as $tag) {
		  TagAR::model()->minusTagCount($tag);
		}
		// update top contents
		LikeAR::model()->saveTopOfDay($nodeAr);
		LikeAR::model()->saveTopOfMonth($nodeAr);

		$this->cleanCache("node_")
			->cleanCache("comment_");
		return $this->responseJSON($nodeAr->attributes, "success");
	}

	/**
	 * Get the page num by nid
	 */
	public function actionGetPageByNid(){
		$request = Yii::app()->getRequest();
		$nid = $request->getParam("nid");
		$pagenum = $request->getParam("pagenum");
		$page = NodeAR::model()->getPageByNid($nid, $pagenum);
		$this->responseJSON($page, "success");
	}


	/**
	 * Get node list
	 */
	public function actionList() {
		$request= Yii::app()->getRequest();

		$token      = $request->getParam("token");
		$type		= $request->getParam("type");
		$country_id	= $request->getParam("country_id");
		$uid		= $request->getParam("uid");
		$mycomment	= $request->getParam("mycomment");
		$mylike		= $request->getParam("mylike");
		$topday		= $request->getParam("topday");
		$topmonth	= $request->getParam("topmonth");
		$page		= $request->getParam("page");
		$pagenum	= $request->getParam("pagenum");
		$start		= $request->getParam("start");
		$end		= $request->getParam("end");
		$orderby 	= $request->getParam("orderby");
		$status 	= $request->getParam("status");
		$keyword 	= $request->getParam("keyword");
		$email 		= $request->getParam("email");
		$flagged	= $request->getParam("flagged");

		if (!$page) {
			$page = 1;
		}

		if (!$pagenum) {
			$pagenum = 10;
		}


		$user = UserAR::model()->findByPk(Yii::app()->user->getId());


		// Build Query
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



		// search by user email
		if (Yii::app()->user->checkAccess("isAdmin") && $email) {
			$queryUser = new CDbCriteria();
			$queryUser->addSearchCondition("company_email", $email, true);
			$queryUser->addSearchCondition("personal_email", $email, true, 'OR');
			$users = UserAR::model()->findAll($queryUser);
			if(count($users) > 0) {
				foreach($users as $user) {
					$usersList[] = $user->uid;
				}
				$strUsersList = implode(',', $usersList);
				$query->addCondition($nodeAr->getTableAlias().".uid in (".$strUsersList.")", "AND");
			}
			else {
				$this->responseJSON(array(), "success");
			}
		}

		if (Yii::app()->user->checkAccess("isAdmin") && $status == 'all') {
			if ($user->role == UserAR::ROLE_ADMIN) {
				// get nothing
			}
			else if ($user->role == UserAR::ROLE_COUNTRY_MANAGER) {
				$query->addCondition("country_id = :country_id");
				$query->params[':country_id'] = $user->country_id;
			}
		}
		elseif (Yii::app()->user->checkAccess("isAdmin") && isset($status)) {
			$query->addCondition($nodeAr->getTableAlias().".status = :status", "AND");
			$params[":status"] = $status;
		}
		else {
			$status = NodeAR::PUBLICHSED;
			$query->addCondition($nodeAr->getTableAlias().".status = :status", "AND");
			$params[":status"] = $status;
		}

		// Get like count
		$query->select = "*". ", count(like_id) AS likecount". ",topday_id AS topday" . ",topmonth_id AS topmonth";
		$query->join = 'left join `like` '.' on '. '`like`' .".nid = ". $nodeAr->getTableAlias().".nid";
		$query->join .= ' left join `topday` '.' on '. '`topday`' .".nid = ". $nodeAr->getTableAlias().".nid";
		$query->join .= ' left join `topmonth` '.' on '. '`topmonth`' .".nid = ". $nodeAr->getTableAlias().".nid";
		$query->group = $nodeAr->getTableAlias().".nid";

		// Get content of the day
		if($topday) {
			$query->select = "*". ",topday_id AS topday";
			$query->join = 'right join `topday` '.' on '. '`topday`' .".nid = ". $nodeAr->getTableAlias().".nid";
		}

		// Get contents of the month
		if($topmonth) {
			$query->select = "*". ",topmonth_id AS topmonth";
			$query->join = 'right join `topmonth` '.' on '. '`topmonth`' .".nid = ". $nodeAr->getTableAlias().".nid";
		}

		// Get the content I commented
		if($mycomment) {
			$query->select = "*";
			$query->join = 'right join `comment` on `comment`.nid = '.$nodeAr->getTableAlias().'.nid';
		}

		// Get the content I liked
		if($mylike) {
			$query->select = "*";
			$query->join = 'right join `like` on `like`.nid = '.$nodeAr->getTableAlias().'.nid';
		}

		if($flagged) {
			$query->select = "*";
			$query->join = 'right join `flag` on `flag`.nid = '.$nodeAr->getTableAlias().'.nid';
		}

		$order = "";
		if ($orderby == "datetime") {
			$order .= " ".$nodeAr->getTableAlias().".datetime DESC";
			$query->order = $order;
		}
		else if ($orderby == "like") {
			$order .= "`likecount` DESC";
			$query->order = $order;
		}
		else if ($orderby == "random") {
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


		// Search by keyword
		if ($keyword) {
			$query->addSearchCondition("description", $keyword);
		}

		// Search by hashtag
		$hashtag = $request->getParam("hashtag");
		if ($hashtag) {
			$query->addSearchCondition("hashtag", $hashtag);
		}

		$count = NodeAR::model()->with("user", "country")->count($query);

		$query->limit = $pagenum;
		$query->offset = ($page - 1 ) * $pagenum;
		$query->with = array("user", "country");

		$res = NodeAR::model()->with("user", "country")->findAll($query);

		$retdata = array();
		$commentAr = new CommentAR();
		foreach ($res as $node) {
			$data = $node->attributes;
			$data["description"]    = $node->description;
			$data["likecount"]      = $node->likecount;
			$data["commentcount"]   = $commentAr->totalCommentsByNode($node->nid);
			$data["user"]           = $node->user ? $node->user->attributes : array();
			$data["country"]        = $node->country ? $node->country->attributes: array();
			$data["user_liked"]     = $node->user_liked;
			$data["user_flagged"]   = $node->user_flagged;
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


			// Remove unnecessary JSON data
			if (!Yii::app()->user->checkAccess("isAdmin")) {
				unset($data["user"]['personal_email']);
				unset($data["user"]['company_email']);
				unset($data['from']);
				unset($data["status"]);
			}
			unset($data["user"]['sso_id']);
			unset($data["user"]['password']);
			unset($data["user"]['token']);
			unset($data["user"]['country_id']);
			unset($data["user"]['status']);
			unset($data["user"]['role']);
			unset($data["country"]['country_name']);
			unset($data["country"]['code']);
			unset($data["country"]['flag_icon']);


			$retdata[] = $data;
		}

		if(Yii::app()->user->checkAccess("isAdmin")) {
			$this->responseJSON($retdata, array('total'=>$count));
		}
		else {
			$this->responseJSON($retdata, "success");
		}

	}


	/**
	 * Get Node by nid
	 */
	public function actionGetByNid() {
		if(Yii::app()->user->checkAccess("isAdmin")) {
			$request = Yii::app()->getRequest();
			$nid = $request->getParam("nid");
			$node = NodeAR::model()->findByPk($nid);
			$data = $node->attributes;
			$data['user'] = UserAR::model()->findByPk($node->uid);
			$data['country'] = CountryAR::model()->findByPk($node->country_id);
			$this->responseJSON($data, "success");
		}
	}

	/**
	 * Post the content via Mail
	 */
	public function actionPostByMail() {
		try {
			// Get client IP address
			$ip = $_SERVER['REMOTE_ADDR'];
			// Server IP Address
			$server_ip = $_SERVER["SERVER_ADDR"];
			// If not from same server, we just simply return false
			if (trim($server_ip) != trim($ip)) {
				return $this->responseJSON(null, null, false);
			}
			$request = Yii::app()->getRequest();
			if (!$request->isPostRequest) {
				return $this->responseJSON(null, null, false);
			}
			$userEmail = $request->getPost("user");
			$desc = $request->getPost("desc");
			$user = UserAR::model()->findByAttributes(array("company_email" => $userEmail));
			if (!$user) {
				$user = UserAR::model()->findByAttributes(array("personal_email" => $userEmail));
				if($user) {
					$isPersonalEmail = true;
				}
			}
			if (!$user) {
				$ret = 'Debug Message (To be delete when live): your account not in our database'; //TODO: delete when live
				return $this->responseJSON(null, $ret, false); //if the user not in our database then return nothing
			}
			$begin = 'Bonjour '.$user->firstname.' '.$user->lastname.',\n\n';
			$end = '\n\nL\'équipe SG WALL';
			$begin_en = '\n\n\n\nDear '.$user->firstname.' '.$user->lastname.',\n\n';
			$end_en = '\n\nSG WALL Team';
			if(empty($desc)) {
				$ret = $begin.'S\'il vous plaît écrivez le sujet de l\'email.'.$end
					.$begin_en.'Please write the email subject.'.$end_en;
				return $this->responseJSON(null, $ret, false);
			}
			$uploadFile = CUploadedFile::getInstanceByName("photo");
			if (!$uploadFile) {
				$ret = $begin.'S\'il vous plaît écrivez le sujet de l\'email.'.$end
					.$begin_en.'Please attach photo or video in attachment.'.$end_en;
				return $this->responseJSON(null, $ret, false);
			}
			else {
				//$mime = $uploadFile->getType();
				exec("/usr/bin/file -b --mime {$uploadFile->tempName}", $output, $status);
				$mimeArray = explode(';',$output[0]);
				$mime = $mimeArray[0];
				$size = $uploadFile->getSize();
				$allowPhotoMime = array(
					"image/gif", "image/png", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png"
				);
				$allowVideoMime = array(
					"application/x-empty" ,
                    "video/mp2p" ,
                    "video/mov",
                    "video/quicktime",
                    "video/x-msvideo",
                    "video/x-ms-wmv",
                    "video/wmv",
                    "video/mp4",
                    "video/avi",
                    "video/3gp",
                    "video/3gpp",
                    "video/mpeg",
                    "video/mpg",
                    "application/octet-stream",
                    "video/x-ms-asf",
                    "video/x-ms-dvr",
                    "video/x-ms-wm",
                    'video/x-ms-wmv',
                    'video/x-msvideo',
                    'video/x-ms-asx',
                    'video/x-ms-wvx',
                    'video/x-ms-wmx',
                    'application/x-troff-msvideo',
                );

				if (in_array($mime, $allowPhotoMime)) {
					$type = 'photo';
					if($size > 5 * 1024000) {
						$ret = $begin.'La taille de votre fichier image ne ​​doit pas dépasser 5 Mo'.$end
							.$begin_en.'The size of your image file should not exceed 5MB'.$end_en;
						return $this->responseJSON(false, $ret, false);
					}
					list($w, $h) = getimagesize($uploadFile->tempName);
					if($w < 450 || $h < 450) {
						$ret = $begin.'Pour avoir une résolution optimale, merci d\'utiliser un format d\'au moins 450x450px'.$end
							.$begin_en.'For optimal resolution, please use a format of at least 450x450 px'.$end_en;
						return $this->responseJSON(false, $ret, false);
					}
				}
				else if (in_array($mime, $allowVideoMime)) {
					$type = 'video';
					if($size > 7 * 1024000) {
						$ret = $begin.'La taille de votre fichier image ne ​​doit pas dépasser 7 Mo'.$end
							.$begin_en.'The size of your image file should not exceed 7MB'.$end_en;
						return $this->responseJSON(false, $ret, false);
					}
				}
				else {
					$ret = $begin.'Le type de fichier que vous venez de télécharger n\'est pas supporté.'.$end
						.$begin_en.'The file you upload is not support.Mo'.$end_en;
					return $this->responseJSON(false, $ret, false);
				}
			}

			$node = new NodeAR();

			$file = $node->saveUploadedFile($uploadFile);

			if(!$file) {
				$ret = $begin.'Le fichier que vous téléchargez est corrompu ou non soutenir.'.$end
					.$begin_en.'The file you upload is corrupted or not support.'.$end_en;
				return $this->responseJSON(false, $ret, false);
			}
			$node->uid          = $user->uid;
			$node->country_id   = $user->country_id;
			$node->type         = $type;
			if(isset($isPersonalEmail) && $isPersonalEmail == true) {
				$node->status   = 0;
				$node->from = 2;
			}
			else {
				$node->status   = 1;
				$node->from = 1;
			}

			$node->file         = $file;
			$node->description  = htmlspecialchars($desc);

			if ($node->validate()) {
				$success = $node->save();
				if (!$success) {
					return $this->responseJSON(false, null, false);
				}
			}
			else {
				return $this->responseJSON(false, null, false);
			}

            $this->cleanAllCache();

			$siteDomain = Yii::app()->params['siteDomain'];

			if(isset($isPersonalEmail) && $isPersonalEmail == true) {
				$ret = $begin.'Votre '.$type.' a été postée. Vous pourrez la voir sur le wall après validation.'.$end
					.$begin_en.'Your '.$type.' is success submit, after approved, you can visit the '.$type.' on the wall.'.$end_en;
			}
			else {
				$ret = $begin.'Votre '.$type.' a été postée, vous pouvez la voir via l\'url:\n'.$siteDomain.'/#/nid/'.$node->nid.$end
					.$begin_en.'Your '.$type.' is success submit, after approved, you can visit the '.$type.' via this url:\n'.$siteDomain.'/#/nid/'.$node->nid.$end_en;
			}

			return $this->responseJSON(true, $ret, false);
		}
		catch (Exception $e) {
			return $this->responseJSON(false, $e->getMessage(), false);
		}
	}
  

}

