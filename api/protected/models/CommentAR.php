<?php

class CommentAR extends CActiveRecord {
  
  public $commentscount = 0;
  
  public $commentcountinnode = 0;
  
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
  
  public function beforeSave() {
    // 设置默认时间
    if (!$this->getAttribute("datetime")) {
      $this->setAttribute("datetime", time());
    }
    return TRUE;
  }
  
  public function totalCommentsByUser($uid) {
		$query = 'select count(*) as count from (SELECT nid FROM `comment` where uid = '.$uid.' group by nid) `comment`';
		$res = Yii::app()->db->createCommand($query)->queryRow();
		return $res['count'];
  }
  
  public function totalCommentsByNode($nid) {
    $query = new CDbCriteria();
    $query->select = "count(*) as commentcountinnode";
    $query->addCondition("nid=:nid");
    $query->params[":nid"] = $nid;
    
    $res = $this->find($query);
    
    return $res->commentcountinnode;
  }

	public function flaggedCommentsList($nid) {
		if ($uid = Yii::app()->user->getId()) {
			$flags = FlagAR::model()->findAllByAttributes(array("comment_nid" => $nid, "uid" => $uid));
			return $flags;
		}
	}
}

