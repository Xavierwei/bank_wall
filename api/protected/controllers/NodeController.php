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
    $uid = 1;
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
        $nodeAr->save();
        $retdata = $nodeAr->attributes;
        // user
        $retdata->user = $nodeAr->user->attributes;
        $retdata->country = $nodeAr->country->attributes;
        
        $this->responseJSON($retdata, "success");
      }
      else {
        $this->responseError(print_r($nodeAr->getErrors(), TRUE));
      }
    }
    else {
      $this->responseError("unknown error");
    }
  }
}

