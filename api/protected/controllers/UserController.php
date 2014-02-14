<?php

class UserController extends Controller {

  public function actionIndex() {
    return $this->responseError("not support");
  }
  
  /**
   * 获取用户列表
   */
  public function actionList() {
    $request = Yii::app()->getRequest();
    
    // 验证权限
    if (!Yii::app()->user->checkAccess("listAllAccount")) {
      return $this->responseError("permission deny");
    }
    
    $role = $request->getParam("role");
    $country_id = $request->getParam("country_id");
    $orderby = $request->getParam("orderby");

    // 验证参数
    // 国家必须是数字
    if ($country_id && !is_numeric($country_id)) {
      $country_id = FALSE;
    }
    
    // 在这里有一个逻辑:
    // 当用户是 country manager 的时候，我们只取这个user 所属的 country_id的用户， 所以 country_id 不管传入参数如何，都要重置
    if (Yii::app()->user->role == UserAR::ROLE_COUNTRY_MANAGER) {
      $login_uid = Yii::app()->user->getId();
      $user = UserAR::model()->findByPk($login_uid);
      $country_id = $user->country_id;
    }

    // Role 必须是数字
    if (!$role) {
      //$this->responseError("invalid params role");
    }
    if ($role && !is_numeric($role)) {
      $this->responseError("invalid params role");
    }

    // orderby 必须是数据库字段
    $columns = UserAR::model()->getTableSchema()->columns;
    if (!isset($columns[$orderby])) {
      $this->responseError("invalid params orderby");
    }
    
    $params = array();
    $query = new CDbCriteria();
    if ($role) {
      $query->addCondition("role=:role");
      $params[":role"] = $role;
    }


    if ($country_id) {
      $query->addCondition(UserAR::model()->tableAlias . ".country_id = :country_id");
      $params[":country_id"] = $country_id;
    }

    $query->order = $orderby . " DESC";

    $query->params = $params;

    $users = UserAR::model()->with("country")->findAll($query);

    // 查询后，组装返回的数据
    $array_users = array();
    foreach ($users as $user) {
      $array = $user->attributes;
      $country = $user->country;
      $array["country"] = $country->attributes;

      $array_users[] = $array;
    }

    $allow_fields = array("uid", "firstname", "lastname", "avatar", "country" => array("country_name", "flag_icon"),
        "company_email", "personal_email", "role", "status");

    $ret_data = array();
    foreach ($array_users as $user) {
      $ret_user = array();
      foreach ($allow_fields as $key => $field) {
        if (is_numeric($key)) {
          $ret_user[$field] = $user[$field];
        } else {
          if (is_array($field)) {
            $ret_user[$key] = array();
            foreach ($field as $sub_field) {
              $ret_user[$key][$sub_field] = $user[$key][$sub_field];
            }
          }
        }
      }
      $ret_data[] = $ret_user;
    }

    $this->responseJSON($ret_data, "success");
  }
  
  /**
   * 用uid 获取用户资料
   */
  public function actionGetByUid() {
    $request = Yii::app()->getRequest();
    $uid = $request->getParam("uid");
    
    if (!Yii::app()->user->role == UserAR::ROLE_ADMIN && !Yii::app()->user->role == UserAR::ROLE_COUNTRY_MANAGER) {
      return $this->responseError("permission deny");
    }
    
    if ($uid) {
      $user = UserAR::model()->with("country")->findByPk($uid);
      if ($user) {
        $ret_user = UserAR::getOutputRecordInArray($user);
        $this->responseJSON($ret_user, "success");
      }
      else {
        $this->responseError("not found user");
      }
    }
    else {
      $this->responseError("invalid params");
    }
  }

  /**
   * Post/ 添加用户
   */
  public function actionPost() {
    $arUser = new UserAR();

    $request = Yii::app()->getRequest();
    
    // 检查用户是否登陆了
    if (Yii::app()->user->getId() ) {
      return $this->responseError("you have already login");
    }

    // Only allow post
    if ($this->isPost()) {
      $id = $arUser->postNewUser();
      if ($id) {
        $mUser = UserAR::model()->findByPk($arUser->uid);
        // 返回数据
        $this->responseJSON(array(
            "uid" => $id,
            "lastname" => $mUser->lastname,
            "firstname" => $mUser->firstname,
            "avatar" => $mUser->avatar,
                ), Yii::t("strings", "success"));
      } else {
        $this->responseError($arUser->errorsString());
      }
    } else {
      $this->responseError(Yii::t("strings", "http error"));
    }
  }


  /**
   * Upload avatar
   */
  public function actionSaveAvatar(){
		$uid = Yii::app()->user->getId();
		if( empty( $uid ) ){
			$this->responseError( "not login" );
		}
		$user = UserAR::model()->findByPk($uid);


		if (!Yii::app()->user->checkAccess("updateOwnAccount", array("uid" => $user->uid ))) {
			return $this->responseError("permission deny");
		}

    $request = Yii::app()->getRequest();

    $iframe = $request->getPost("iframe");
    if($iframe) {
      $fileUpload = CUploadedFile::getInstanceByName("file");
      $validateUpload = NodeAR::model()->validateUpload($fileUpload, 'photo');
      if($validateUpload !== true) {
        return $this->responseError($validateUpload);
      }

      // save file to dir
      $fileUpload = NodeAR::model()->saveUploadedFile($fileUpload);

      $size = getimagesize(ROOT . '/' . $fileUpload);
      $s_w = $size[0];
      $s_h = $size[1];
      $w = $h = 80;

      $r1 = $w / $s_w;
      $r2 = $h / $s_h;
      $widthSamller = TRUE;
      if ($r1 > $r2) {
        $r = $r1;
      }
      else {
        $widthSamller = FALSE;
        $r = $r2;
      }
      $t_w = $r * $s_w;
      $t_h = $r * $s_h;

      // 先等比例 resize
      $thumb = new EasyImage(ROOT . '/' . $fileUpload);
      $thumb->resize($t_w, $t_h);
      // 再裁剪
      // 裁剪 多余的宽
      if (!$widthSamller) {
        $start_x = ($t_w - $w)/2;
        $start_y = 0;
        $thumb->crop($w, $h, $start_x, $start_y);
      }
      // 裁剪多余的 高
      else {
        $start_x = 0;
        $start_y = ($t_h - $h);
        $thumb->crop($w, $h, $start_x, $start_y);
      }

      unlink(ROOT . '/' . $fileUpload);
      $fileto = ROOT . '/uploads/avatar/' . $uid .'.'. pathinfo($fileUpload, PATHINFO_EXTENSION);
      $thumb->save($fileto);
      $fileto = str_replace( ROOT, '', $fileto );
      $this->render('post', array(
        'url'=>$fileto
      ));
    }
    else {
      $fileUpload = $request->getPost("file");
      if( empty( $fileUpload ) ){
        $this->responseError('no file');
      }
      // cut the file
      $thumb = new EasyImage( ROOT . '/' . $fileUpload );
      $thumb->resize( $request->getPost("width") , $request->getPost("height") );
      $thumb->crop( 220 , 220 , -$request->getPost("x") , -$request->getPost("y") );
      $thumb->resize(80, 80);
      $fileto = ROOT . '/uploads/avatar/' . $uid .'.'. pathinfo($fileUpload, PATHINFO_EXTENSION);
      $dir = dirname( $fileto );
      if (!is_dir($dir)) {
        mkdir($dir, 0777, TRUE);
      }
      $thumb->save( $fileto );
      unlink(ROOT . '/' . $fileUpload);
      $fileto = str_replace( ROOT, '', $fileto );
      UserAR::model()->updateByPk($uid, array('avatar' => $fileto ));
      $this->responseJSON(array( "file" => $fileto ) , "success");
    }


  }
  /**
   * 删除用户
   */
  public function actionDelete() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $uid = $request->getPost("uid");
    if (!$uid) {
      $this->responseError("invalid params");
    }
    $user = UserAR::model()->with("country")->findByPk($uid);
    
    // 检查用户权限
    if (!Yii::app()->user->checkAccess("deleteAnyAccount", array("country_id" => $user->country_id))) {
      return $this->responseError("permission deny");
    }
    
    if ($user) {
      if (!Yii::app()->user->checkAccess("deleteAnyAccount", array("country_id" => $user->country_id))) {
        return $this->responseError("permission deny");
      }
      UserAR::model()->deleteByPk($user->uid);
      $this->responseJSON(array(), "success");
    }
    else {
      $this->responseJSON(array(), "success");
    }
  }

	/**
	 * Get current user status
	 */
	public function actionGetCurrent() {

    if ($uid = Yii::app()->user->getId()) {
      $user = UserAR::model()->with("country")->findByPk($uid);
      $country = $user->country;
      $retdata = $user->getOutputRecordInArray(array("country" => $country) + $user->attributes);

      // Get likes count
      $likeAr = new LikeAR();
      $likecount = $likeAr->totalLikeByUser($user->uid);
      $retdata["likes_count"] = $likecount;
      
      // Get comments count
      $commentAr = new CommentAR();
      $retdata["comments_count"] = $commentAr->totalCommentsByUser($user->uid);
      
      // Get photos/photos counts
      $nodeAr = new NodeAR();
      $retdata["photos_count"] = $nodeAr->countByType($user->uid, 'photo');
      $retdata["videos_count"] = $nodeAr->countByType($user->uid, 'video');
      $retdata["count_by_day"] = $nodeAr->countByDay($user->uid);
      $retdata["count_by_month"] = $nodeAr->countByMonth($user->uid);
      
      $this->responseJSON($retdata, "success");
    }
    else {
      $this->responseError("not login");
    }
  }
  
  /**
   * 更改用户资料 
   */
  public function actionPut() {
    $request = Yii::app()->getRequest();
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $uid = Yii::app()->user->getId();
    
    if ($uid) {
      $user = UserAR::model()->findByPk($uid);
      if (!$user) {
        $this->responseError("invalid params");
      }


      if (!Yii::app()->user->checkAccess("updateOwnAccount", array("uid" => $user->uid ))) {
        if (!Yii::app()->user->checkAccess("updateAnyAccount", array("country_id" => $user->country_id))) {
          return $this->responseError("permission deny2");
        }
        return $this->responseError("permission deny1");
      }
      
      $data = $_POST;
      $update_uid = $data['uid'];
      unset($data['uid']);
      // 使用单点登陆后无需再修改密码，因此注销以下代码
//      foreach ($data as $key => $value) {
//        if ($key == "password") {
//          $user->setAttribute($key, md5($value));
//        }
//        else {
//          $user->setAttribute($key, $value);
//        }
//      }
      UserAR::model()->updateByPk($update_uid, $data);
      
      $this->responseJSON($user, "success");
    }
    else {
      $this->responseError("not login");
    }
  }

	/**
	 * SAML SSO Login
	 * TODO: Need change to live SAML IdP
	 */
	public function actionSAMLLogin() {
		$as = new SimpleSAML_Auth_Simple('default-sp');
		$as->requireAuth();
		$attributes = $as->getAttributes();
		if(!$attributes) {
			return $this->responseError("login failed");
		}

		// Create the new user if user doesn't exist in database
		if( !$user = UserAR::model()->findByAttributes(array('company_email'=>$attributes['eduPersonPrincipalName'][0])) ) {
			$user = UserAR::model()->createSAMLRegister($attributes);
		}

		// Identity local site user data
		$userIdentify = new UserIdentity($user->company_email, $attributes['eduPersonTargetedID'][0]);

		// Save user status in session
		if (!$userIdentify->authenticate()) {
			$this->responseError("login failed");
		}
		else {
			Yii::app()->user->login($userIdentify);
			$this->redirect('../../index');
		}
	}

	/**
	 * SAML Logout
	 */
	public function actionSAMLLogout() {
		$uid = Yii::app()->user->getId();
		$user = UserAR::model()->findByPk($uid);
		if ($user) {
			// Clean session
			Yii::app()->user->logout();
			// Logout from SSO
			$as = new SimpleSAML_Auth_Simple('default-sp');
			$status = $as->isAuthenticated();
			if($status){
				$as->logout();
			}
			else {
				$this->redirect('../../index');
			}
		}
		else {
			$this->redirect('../../index');
		}
	}
}
