<?php

class TagAR extends CActiveRecord {
  public function tableName() {
    return "tag";
  }
  
  public function primaryKey() {
    return "tag_id";
  }
  
  public static function model($class = __CLASS__) {
    return parent::model($class);
  }
  
  public function rules() {
    return array(
        array("tag_id", "safe"),
    );
  }
  
  public function beforeSave() {
    // 设置默认时间
    if (!$this->getAttribute("datetime")) {
      $this->setAttribute("datetime", time());
    }
    return TRUE;
  }
  
  public function searchTag($keyword) {
      $tags = $this->findAll();
      return $tags;
  }

}

