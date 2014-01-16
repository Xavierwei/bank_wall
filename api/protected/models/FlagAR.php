<?php

/**
 * @author  jackey <jziwenchen@gmail.com>
 */
class FlagAR extends CActiveRecord {
  
  public $flagcount = 0;
  
  // 3 次 flag 后， 把node 放到 blocked 状态下
  const COUNT_THAT_BLOckED = 3;
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
  
  public function afterSave() {
    $nid = $this->nid;
    
    if ($nid) {
      // 保存后， 查询这个nid 有 COUNT_THAT_BLOckED 个 flag.
      $command = Yii::app()->db->createCommand("SELECT count(*) as count FROM flag where nid = :nid");
      $res = $command->query(array(":nid" => $nid))->read();
      if ($res["count"] >= self::COUNT_THAT_BLOckED) {
        $node = NodeAR::model()->findByPk($nid);
        $node->blockIt();
      }
    }
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
    $query->params[":cid"] = $cid;

    return $this->deleteAll($query);
  }
  
  public function relations() {
    return array(
        "node" => array(self::BELONGS_TO, "NodeAR", "nid"),
        "comment" => array(self::BELONGS_TO, "CommentAR", "cid"),
    );
  }
  
  public function flagCountInNode($nid) {
    $query = new CDbCriteria();
    $query->select = "count(*) as flagcount";
    $query->addCondition("nid=:nid");
    $query->params[":nid"] = $nid;
    
    $res = $this->find($query);
    
    return $res->flagcount;
  }
}

