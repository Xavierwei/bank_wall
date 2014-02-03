<?php


class LikeController extends Controller {
  
  public function actionPost() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }

    if(Yii::app()->user->isGuest) {
      return $this->responseError("need login");
    }
    
    if (!Yii::app()->user->checkAccess("flagNode")) {
      return $this->responseError("permission deny");
    }
    
    $uid = Yii::app()->user->getId();
    //$uid = UserAR::model()->find()->uid;
    $nid = $request->getPost("nid");
    
    $likeAr = new LikeAR();
    $likeAr->attributes = array(
        "uid" => $uid,
        "nid" => $nid
    );

    $currentUserLikeCount = $likeAr->getUserNodeCount($nid,$uid);
    
    // 验证/ 然后 保存
    if ($likeAr->validate()) {
      // 验证是否已投过
      if($currentUserLikeCount == 0) {
        $likeAr->save();
        $this->responseJSON($likeAr->getNodeCount($nid), "success");
      }
      else
      {
        $this->responseError('Already Liked');
      }
    }
    else {
      $this->responseError(current(array_shift($likeAr->getErrors())));
    }
  }
  
  public function actionDelete() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }

    if(Yii::app()->user->isGuest) {
      return $this->responseError("need login");
    }
    
    // 这里不需要检查权限，因为用户如果like了 就取消掉； 如果没有like过 就什么也不做
//    if (!Yii::app()->user->checkAccess("cancelOwnLike", array("uid" => $uid))) {
//      return $this->responseError("permission deny");
//    }
    
    $uid = Yii::app()->user->getId();
    //$uid = UserAR::model()->find()->uid;
    $nid = $request->getPost("nid");
    
    $likeAr = new LikeAR();
    $likeAr->deleteLike($uid, $nid);

    $this->responseJSON($likeAr->getNodeCount($nid), "success");
  }
}

