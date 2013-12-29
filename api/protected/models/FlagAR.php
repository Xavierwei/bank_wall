<?php

/**
 * @author  jackey <jziwenchen@gmail.com>
 */
class FlagAR extends CActiveRecord {
  public function tableName() {
    return "flag";
  }
  
  public function primaryKey() {
    return "flag_id";
  }
  
  public static function model($class = __CLASS__) {
    return parent::model($class);
  }
  
  public function rules() {
    return array(
        array("nid", "NidExist"),
        array("uid", "UidExist"),
        array("cid", "CidExist"),
        array("datetime, flag_id", "safe"),
    );
  }
  
  public function beforeSave() {
    // 设置默认时间
    if (!$this->getAttribute("datetime")) {
      $this->setAttribute("datetime", time());
    }
    return TRUE;
  }
  
  // 删除Node Like
  public function deleteNodeFlag($nid) {
    $query = new CDbCriteria();
    $query->addCondition("nid = :nid");
    $query->params[":nid"] = $nid;

    return $this->deleteAll($query);
  }
  
  // 删除Comment Like
  public function deleteCommentFlag($cid) {
    $query = new CDbCriteria();
    $query->addCondition("cid = :cid");
    $query->params[":cid"] = $nid;

    return $this->deleteAll($query);
  }
  
  public function relations() {
    return array(
        "node" => array(self::BELONGS_TO, "NodeAR", "nid"),
        "comment" => array(self::BELONGS_TO, "CommentAR", "cid"),
    );
  }
}

