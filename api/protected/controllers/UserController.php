<?php

class UserController extends Controller {

	public function actionIndex() {
		//return $this->responseError(101);
	}

	/**
	 * SAML SSO Login
	 * TODO: Need change to live SAML IdP
	 */
	public function actionSAMLLogin() {
		if(isset($_SERVER['HTTP_REFERER'])) {
			$referer = $_SERVER['HTTP_REFERER'];
			if(strpos($referer, 'admin') > 0)
			{
				Yii::app()->session['loginfrom'] = 'admin';
			}
		}

		// TODO: TESTING
		$as = new SimpleSAML_Auth_Simple('default-sp');
		// TODO: LIVE
		//$as = new SimpleSAML_Auth_Simple('live-sp');

		$as->requireAuth();
		$attributes = $as->getAttributes();
		if(!$attributes) {
			return $this->responseError("login saml failed");
		}

		// TODO: TESTING
		// Create the new user if user doesn't exist in database
		if( !$user = UserAR::model()->findByAttributes(array('company_email'=>$attributes['eduPersonPrincipalName'][0])) ) {
			$user = UserAR::model()->createSAMLRegisterTest($attributes);
		}

		// Identity local site user data
		$userIdentify = new UserIdentity($user->company_email, $attributes['uid'][0]);

		// Save user status in session
		if (!$userIdentify->authenticate()) {
			$this->responseError("login failed.");
		}
		else {
			Yii::app()->user->login($userIdentify);
			if(Yii::app()->session['loginfrom'] == 'admin') {
				$this->redirect('../admin/index');
			}
			else {
				$this->redirect('../../index');
			}
		}


		// TODO: LIVE
		// Create the new user if user doesn't exist in database
//		if( !$user = UserAR::model()->findByAttributes(array('company_email'=>$attributes['sggroupid'][0])) ) {
//			$user = UserAR::model()->createSAMLRegister($attributes);
//		}
//
//		// Identity local site user data
//		$userIdentify = new UserIdentity($user->company_email, $attributes['uid'][0]);
//
//		// Save user status in session
//		if (!$userIdentify->authenticate()) {
//			$this->responseError("login failed.");
//		}
//		else {
//			Yii::app()->user->login($userIdentify);
//			if(Yii::app()->session['loginfrom'] == 'admin') {
//				$this->redirect('../admin/index');
//			}
//			else {
//				$this->redirect('../../index');
//			}
//		}
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



	/**
	* Update user
	*/
	public function actionPut() {
		$request = Yii::app()->getRequest();
		if (!$request->isPostRequest) {
			$this->responseError(101);
		}

		$uid = Yii::app()->user->getId();

		if ($uid) {
			$user = UserAR::model()->findByPk($uid);
			if (!$user) {
				$this->responseError(101);
			}


			if (!Yii::app()->user->checkAccess("updateOwnAccount", array("uid" => $user->uid ))) {
				return $this->responseError(602);
			}

			$data = $_POST;
			$query = new CDbCriteria();
			$query->addCondition('uid != :uid');
			$query->params[":uid"] = $uid;
			$existUserCount = UserAR::model()->countByAttributes(array("personal_email"=>$data['personal_email']), $query);
			if($existUserCount > 0) {
				return $this->responseError(603); // Personal Email already exsit
			}

			UserAR::model()->updateByPk($uid, $data);

			$this->responseJSON('', "success");
		}
		else {
			$this->responseError(601);
		}
	}


	/**
	* Upload avatar
	*/
	public function actionSaveAvatar(){
		$uid = Yii::app()->user->getId();
		$request = Yii::app()->getRequest();
		$iframe = $request->getPost("iframe");
		if( empty( $uid ) ){
			if($iframe) {
				$this->render('post', array(
					'code'=>601
				));
				return;
			}
			else {
				return $this->responseError(601);
			}
		}
		$user = UserAR::model()->findByPk($uid);
		if (!Yii::app()->user->checkAccess("updateOwnAccount", array("uid" => $user->uid ))) {
			return $this->responseError("permission deny");
		}
		if($iframe) {
			$fileUpload = CUploadedFile::getInstanceByName("file");
			$validateUpload = NodeAR::model()->validateUpload($fileUpload, 'avatar');
			if($validateUpload !== true) {
				$this->render('post', array(
					'code'=>$validateUpload
				));
				return;
			}

			// save file to dir
			$fileUpload = NodeAR::model()->saveUploadedFile($fileUpload);
			if($fileUpload) {
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
				$this->render('post', array(
					'code'=>'509'
				));
			}
		}
		else {
			$file_id = $request->getPost("file");
			$fileUpload = NodeAR::getFile($file_id, $uid);
			if( empty( $fileUpload ) ){
				$this->responseError('no file');
			}
			// cut the file
			$thumb = new EasyImage( ROOT . '/' . $fileUpload );
			$thumb->resize( $request->getPost("width") , $request->getPost("height") );
			$thumb->crop( $request->getPost("size") , $request->getPost("size") , -$request->getPost("x") , -$request->getPost("y") );
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
			if(isset($file_id)) {
				NodeAR::deleteFile($file_id);
			}
			$this->responseJSON(array( "file" => $fileto ) , "success");
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

			// Remove unnecessary JSON data
			unset($retdata["role"]);
			unset($retdata["status"]);
			unset($retdata["role"]);
			unset($retdata["country"]["country_name"]);
			unset($retdata["country"]["flag_icon"]);

			// Generate random token string
			$token = Drtool::randomNew();
			Drtool::setMyCookie('sg_token', $token);
			// Save the token to database
			UserAR::model()->updateByPk($user->uid, array('token' => $token ));

			$this->responseJSON($retdata, "success");
		}
		else {
		    $this->responseError(601);
		}
	}


	/**
	 * Generate API token, when call the GET api, server side need validate the token from client side
	 */
	public function actionGetToken() {
		$token = UserAR::model()->getToken();
		$this->responseJSON($token, "success");
	}



}
