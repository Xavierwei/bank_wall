<?php

class CommentAR extends CActiveRecord {
  public function tableName() {
    return "comment";
  }
  
  public function primaryKey() {
    return "cid";
  }
  
  public static function model($class = __CLASS__) {
    return parent::model($class);
  }
  
  public function rules() {
    return array(
        array("uid", "UidExist"),
        array("nid", "NidExist"),
        array("content, datetime, cid", "safe"),
    );
  }
  
  public function relations() {
    return array(
        "user" => array(self::BELONGS_TO, "UserAR", "uid"),
        "node" => array(self::BELONGS_TO, "NodeAR", "nid"),
    );
  }
}

