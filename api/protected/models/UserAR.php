<?php

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
    return array("uid", "firstname", "lastname", "avatar", "country" => array("country_id", "country_name", "flag_icon"),
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
            if (isset($user[$key])) {
              $ret_user[$key][$sub_field] = $user[$key][$sub_field];
            }
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
        //array("name", "dbRowUnique"),
        //array("personal_email", "email"),
        //array("company_email", "email"),
        //array("personal_email", "dbRowUnique"),
				//array("sso_id", "dbRowUnique"),
        //array("role", "type", 'type' => 'int'),
        //array("password", "required"),
        array("avatar,personal_email,company_email,name,sso_id, country_id,datetime, firstname, lastname, role", 'safe'),
        //array("country_id", "required"),
    );
  }
  
  // 静态方法，返回 model
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function beforeSave() {

    

    return TRUE;
  }
  
  
  // 初始化操作
  public function init() {
    parent::init();
  }
  
  // Validator
  //  相当于 unique key
  public function dbRowUnique($attribute, $pramas = array()) {
    $value = $this->{$attribute};
    
    $model = self::model();
    $row = $model->findByAttributes(array("$attribute" => $value));
    
    if ($row) {
      $this->addError($attribute, Yii::t("strings", "$attribute duplicated"));
      return FALSE;
    }
    return  TRUE;
  }

	public function createSAMLRegister($attributes) {
		$newUser = new UserAR();
		$newUser->datetime = time();
		$newUser->name = $attributes['uid'][0];
		$newUser->company_email = $attributes['eduPersonPrincipalName'][0];
		$newUser->sso_id = md5($attributes['eduPersonTargetedID'][0]);
		$newUser->firstname = $attributes['givenName'][0];
		$newUser->lastname = $attributes['sn'][0];
		$newUser->role = self::ROLE_AUTHEN;
		$newUser->country_id = 30;

		if ($newUser->validate()) {
			$newUser->save();
			return $newUser;
		}
		else {
			return FALSE;
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
    if (!$this->getAttribute("name")) {
      $this->setAttribute("name", $this->getAttribute("company_email"));
    }
    // 默认情况下， 添加新用户时 role 是 ROLE_AUTH
    if (!$this->getAttribute("role")) {
      $this->setAttribute("role", self::ROLE_AUTHEN);
    }
    
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
        "country" => array(self::BELONGS_TO, "CountryAR", "country_id"),
    );
  }
}
