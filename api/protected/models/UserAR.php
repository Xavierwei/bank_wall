<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserAR
 *
 * @author jackey
 */
class UserAR extends CActiveRecord{
  
  const USER_DELETED = -2;
  const USER_DISABLED = -1;
  const USER_ACTIVE = 1;
  
  const ROLE_ADMIN = 2;
  const ROLE_COUNTRY_MANAGER = 3;
  const ROLE_AUTHEN = 1;
  const ROLE_GUEST = 0;
  
  public function tableName() {
    return "user";
  }
  
  public static function getAllowOutputFields() {
    return array("uid", "firstname", "lastname", "avatar", "country" => array("country_name", "flag_icon"),
        "company_email", "personal_email", "role", "status");
  }
  
  public static  function getOutputRecordInArray($user) {
    $allow_fields = self::getAllowOutputFields();
    $ret_user = array();
    foreach ($allow_fields as $key => $field) {
      if (is_numeric($key)) {
        $ret_user[$field] = $user[$field];
      } else {
        if (is_array($field)) {
          $ret_user[$key] = array();
          foreach ($field as $sub_field) {
            $ret_user[$key][$sub_field] = $user[$key][$sub_field];
          }
        }
      }
    }
    
    return $ret_user;
  }


  public function getPrimaryKey() {
    return "uid";
  }
  
  // Validation rules
  public function rules() {
    return array(
        array("name", "dbRowUnique"),
        array("personal_email", "email"),
        array("personal_email", "dbRowUnique"),
        array("company_email", "email"),
        array("personal_email", "dbRowUnique"),
        array("role", "type", 'type' => 'int'),
        array("password", "required"),
        array("avadar, datetime, firstname, lastname", 'safe'),
        array("country_id", "required"),
    );
  }
  
  // 静态方法，返回 model
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  // 初始化操作
  public function init() {
    parent::init();
  }
  
  public function dbRowUnique($attribute, $pramas = array()) {
    $value = $this->{$attribute};
    
    $model = self::model();
    $row = $model->find("$attribute = :attr", array(":attr" => $attribute));
    
    if ($row && $row->count()) {
      return $this->addError($attribute, Yii::t("strings", "only allow unique value"));
    }
  }
  
  public function postNewUser() {
    $this->attributes = $_POST;
    if (!$this->getAttribute("datetime")) {
      $this->setAttribute("datetime", time());
    }
    if ($this->getAttribute("password")) {
      $this->setAttribute("password", md5($this->getAttribute("password")));
    }
    // 创建新用户时候 不能指定角色；增加安全性.
    $this->role = 0;
    if ($this->validate()) {
      return $this->save();
    }
    else {
      return FALSE;
    }
  }
  
  public function errorsString() {
    $errors = $this->getErrors();
    $str = "";
    foreach ($errors as $type => $error) {
      foreach ($error as $msg) {
        $str = "\r\n$msg";
      }
    }
    return $str;
  }
  
  public function relations() {
    return array(
        "country" => array(self::HAS_ONE, "CountryAR", "country_id"),
    );
  }
}
