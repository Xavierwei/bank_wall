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
        array("country", "countryExist"),
        array("file, type, datetime, status, description", "safe"),
    );
  }
  
  public function uidExist($attribute, $params = array()) {
    $uid = $this->{$attribute};
        
    if ($uid) {
      $user = UserAR::model()->findByPk($uid);
      if ($user) {
        $this->addError($attribute, "user is not exist our system");
      }
    }
  }
  
  public function countryExist($attribute, $params = array()) {
    $country_id = $this->{$attribute};
        
    if ($country_id) {
      $country = CountryAR::model()->findByPk($country_id);
      if (country) {
        $this->addError($attribute, "country is not exist our system");
      }
    }
  }
  
  public function relations() {
    return array(
        "country" => array(self::HAS_ONE, "CountryAR", "country_id"),
        "user" => array(self:: HAS_ONE, "UserAR", "uid"),
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
    $this->hashtag = json_encode($hashtags);
    $this->datetime = time();
  }
  
  public function afterFind() {
    parent::afterFind();
    $this->hashtag = json_decode($this->hashtag);
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
    
    return $to;
  }
}
