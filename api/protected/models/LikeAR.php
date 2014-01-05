<?php

/**
 * @author  jackey <jziwenchen@gmail.com>
 */
class LikeAR extends CActiveRecord {
  
  public $likecount = 0;
  
  
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
        array("datetime, like_id", "safe"),
    );
  }
  
  public function beforeSave() {
    // 设置默认时间
    if (!$this->getAttribute("datetime")) {
      $this->setAttribute("datetime", time());
    }
    return TRUE;
  }
  
  // 删除Like
  public function deleteLike($uid, $nid) {
    $query = new CDbCriteria();
    $query->addCondition("nid = :nid");
    $query->addCondition("uid = :uid");
    $query->params[":uid"] = $uid;
    $query->params[":nid"] = $nid;

    return $this->deleteAll($query);
  }
  
  // get total like count 
  public function totalLikeByUser($uid) {
    $query = new CDbCriteria();
    $query->select = array("count(*) AS likecount");
    $query->addCondition("uid = :uid");
    $query->params[":uid"] = $uid;
    
    $res = $this->find($query);
    
    return $res["likecount"];
  }
}

