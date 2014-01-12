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
        array("tag,count,tag_id", "safe")
    );
  }

  
  public function searchTag($term) {
		$query=new CDbCriteria;
		$query->addSearchCondition('tag',$term);
		$query->limit = 5;
		$tags = $this->findAll($query);
		return $tags;
  }

	public function saveTag($term) {
		$query=new CDbCriteria;
		$query->addCondition('tag=:tag');
		$query->params[':tag']=$term;
		$res=$this->find($query);
		if($res) {
			$res->count = $res->attributes['count'] + 1;
			$res->updateByPk($res->tag_id, $res->attributes);
		}
		else {
			$tag = new TagAR();
			$tag->attributes = array(
				"tag" => $term
			);
			$tag->save();
		}

		return $res;
	}
}

