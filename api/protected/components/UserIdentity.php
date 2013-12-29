<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
  private $_id = NULL;
  
  public function __construct($username, $password) {
    parent::__construct($username, $password);
    
    Yii::app()->user->setState("isAdmin", TRUE);
    Yii::app()->user->setState("isCountryManager", FALSE);
    Yii::app()->user->setState("isAuthenticated", FALSE); 
    Yii::app()->user->setState("isGuest", FALSE);
  }
  
  public function authenticate()
  {
    $arUser = new UserAR();
    $user = UserAR::model()->findByAttributes(array("company_email" => $this->username));
    if (!$user) {
      $this->errorCode = self::ERROR_USERNAME_INVALID;
    }
    else if ($user->password != md5($this->password)) {
      $this->errorCode = self::ERROR_PASSWORD_INVALID;
    }
    else {
      $this->_id = $user->uid;
      $this->setState("name", $user->name);
      
      $this->errorCode = self::ERROR_NONE;
    }

    return !$this->errorCode;
  }

  public function getId() {
    return $this->_id;
  }
}