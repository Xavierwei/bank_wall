<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NodeAR
 *
 * @author jackey
 */
class NodeAR extends CActiveRecord{
    
    const PUBLICHSED = 1;
    const UNPUBLISHED = 2;
    const BLOCKED = 3;
    
    
  public $likecount = 0;
  
  public $user_liked = FALSE;
  
  public $like = array();
  
  public static function model($class = __CLASS__) {
    return parent::model($class);
  }
  
  public function tableName() {
    return "node";
  }
  
  public function getPrimaryKey() {
    return "nid";
  }
  
  public function rules() {
    return array(
        array("uid, country_id, type", "required"),
        array("uid", "uidExist"),
        array("country_id", "countryExist"),
        array("file, type, datetime, status, description, nid, hashtag, user_liked, like", "safe"),
    );
  }
  
  public function uidExist($attribute, $params = array()) {
    $uid = $this->{$attribute};
        
    if ($uid) {
      $user = UserAR::model()->findByPk($uid);
      if (!$user) {
        $this->addError($attribute, "user is not exist our system");
      }
    }
  }
  
  public function countryExist($attribute, $params = array()) {
    $country_id = $this->{$attribute};
        
    if ($country_id) {
      $country = CountryAR::model()->findByPk($country_id);
      if (!$country) {
        $this->addError($attribute, "country is not exist our system");
      }
    }
  }
  
  public function relations() {
    return array(
        "country" => array(self::BELONGS_TO, "CountryAR", "country_id"),
        "user" => array(self::BELONGS_TO, "UserAR", "uid"),
    );
  }
  
  public function getHashTag() {
    $description = $this->description;
    
    $matches = array();
    preg_match_all("/(?<!\w)#\w+/", $description, $matches);
    $hashtags = array_shift($matches);
    return $hashtags;
  }
  
  public function beforeSave() {
    parent::beforeSave();
    
    $hashtags = $this->getHashTag();
    // 在添加时 需要制定一个默认的 status = publichsed
    if (!$this->{$this->getPrimaryKey()}) {
      $this->setAttribute("status", self::PUBLICHSED);  
    }
    $this->setAttribute("hashtag", serialize($hashtags));
    $this->setAttribute("datetime", time());
    
    return TRUE;
  }
  
  public function afterFind() {
    parent::afterFind();
    $this->hashtag = unserialize($this->hashtag);
    
    // 加载当前用户的flag
    if ($uid = Yii::app()->user->getId() ) {
      $user = UserAR::model()->findByPk($uid);
      if ($user) {
        $flag = LikeAR::model()->findByAttributes(array("nid" => $this->nid, "uid" => $user->uid));
        if (($flag)) {
          $this->like = $flag->attributes;
          $this->user_liked = TRUE;
        }
        else {
          $this->user_liked = FALSE;
        }
      }
    }
    
    return TRUE;
  }
  
  public function afterSave() {
      $file = $this->file;
      $nid = $this->nid;
      $type = $this->type;
      if ($type == "photo") {
          $name = "p". $this->nid;
      }
      else {
          $name = "v". $this->nid;
      }
      
      $ext = pathinfo($this->file, PATHINFO_EXTENSION);
      $newname = $name.'.'.$ext;
      
      $paths = explode("/", $this->file);
      $paths[count($paths) - 1] = $newname;
      $newpath = implode("/", $paths);
      
      rename(ROOT.$this->file, ROOT. $newpath);
      
      // 文件重命名后 修改数据库
      $this->updateByPk($this->nid, array("file" => $newpath));
      
      $this->file = $newpath;
      
      // Load user/country
      $userAr = new UserAR();
      $userAr->setAttributes($userAr->getOutputRecordInArray(UserAR::model()->findByPk($this->uid)));
      $this->user = $userAr;
      
      $this->country = CountryAR::model()->findByPk($this->country_id);
      
      return TRUE;
  }
  
  /**
   * 
   * @param CUploadedFile $upload
   */
  public function saveUploadedFile($upload) {
    $dir = ROOT."/uploads";
    
    if (!is_dir($dir)) {
      mkdir($dir, 0777, TRUE);
    }
    
    $filename = uniqid().'_'.$upload->getName();
    $to = $dir."/". $filename;
    $upload->saveAs($to);
    
    $to = str_replace(ROOT, "", $to);
    
    return $to;
  }
  
  public function blockIt() {
    if ($this->nid) {
      $this->updateByPk($this->nid, array("status" => self::BLOCKED));
    }
  }
}
