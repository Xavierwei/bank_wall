<?php

/**
 * @author jackey <jziwenchen@gmail.com>
 */
class LoginForm extends CFormModel {
  public $company_email;
  public $password;
  
  private $_identify;
  
  public function rules() {
    return array(
        array("company_email, password", "required"),
        array("company_email", "email"),
        array("password", "authenticate"),
    );
  }
  
  public function authenticate($attribute, $params = array()) {
    $password = $this->{$attribute};
    $this->_identify = new UserIdentity($this->company_email, $password);
    
    if (!$this->_identify->authenticate()) {
      $this->addError($attribute, Yii::t("strings", "email or password wrong"));
    }
  }
  
  public function getUserIdentify() {
    return $this->_identify;
  }
}
