<?php

class LikeAR extends CActiveRecord {
  public function tableName() {
    return "like";
  }
  
  public function primaryKey() {
    return "like_id";
  }
  
  public static function model($class = __CLASS__) {
    return parent::model($class);
  }
  
  public function rules() {
    return array(
        array("nid", "NidExist"),
        array("uid", "UidExist"),
        array("datetime, flag_id", "safe"),
    );
  }
  
  public function beforeSave() {
    if (!$this->getAttribute("datetime")) {
      $this->setAttribute("datetime", time());
    }
    return TRUE;
  }
  
  public function deleteLike($uid, $nid) {
    $query = new CDbCriteria();
    $query->addCondition("nid = :nid");
    $query->addCondition("uid = :uid");
    $query->params[":uid"] = $uid;
    $query->params[":nid"] = $nid;

    return $this->deleteAll($query);
  }
}

