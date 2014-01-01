<?php

/**
 * @author Jackey <jziwenchen@gmail.com>
 */
class LikeController extends Controller {
  
  public function actionPost() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
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
    
    // 验证/ 然后 保存
    if ($likeAr->validate()) {
      $likeAr->save();
      
      $this->responseJSON($likeAr->attributes, "success");
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
    
    // 这里不需要检查权限，因为用户如果like了 就取消掉； 如果没有like过 就什么也不做
//    if (!Yii::app()->user->checkAccess("cancelOwnLike", array("uid" => $uid))) {
//      return $this->responseError("permission deny");
//    }
    
    $uid = Yii::app()->user->getId();
    //$uid = UserAR::model()->find()->uid;
    $nid = $request->getPost("nid");
    
    $likeAr = new LikeAR();
    $likeAr->deleteLike($uid, $nid);
    
    $this->responseJSON(array(), "success");
  }
}

