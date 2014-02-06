<?php

class CountryAR extends CActiveRecord{
  
  public function tableName() {
    return "country";
  }
  
  public function getPrimaryKey() {
    return "country_id";
  }
  
  public static function model($class = __CLASS__) {
    return parent::model($class);
  }
  
  public function rules() {
    return array(
        array("country_name", "required"),
        array("code", "uniqueCountryCode"),
        array("code", "required"),
        array("flag_icon", "required"),
        array("description", "safe"),
    );
  }
  
  public function uniqueCountryCode($attribute, $params = array()) {
    $count = self::model()->findByAttributes(array("code" => $this->{$attribute}));
    if ($count) {
      return $this->addError($attribute, "code duplcated");
    }
  }
  
  public function postNewCountry() {
    $this->attributes = $_POST;
    // 创建新用户时候 不能指定角色；增加安全性.
    $this->setAttribute("country_id", NULL);
    if ($this->validate()) {
        $this->save();
        return $this->country_id;
    }
    else {
      return FALSE;
    }
  }
}
