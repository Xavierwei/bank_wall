<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PhpAuthManager
 *
 * @author jackey
 */
class PhpAuthManager extends CPhpAuthManager{
  
  public function init() {
    parent::init();
    
    // 创建操作
    $this->createOperation("list_all_user", "get all users");
    $this->createOperation("list_user_in_country", "get all users in special country");
    
    // 创建角色
    $bizRule = "return Yii::app()->user->isCountryManager;";
    $role = $this->createRole("country_manager");
    
    // 分配操作
    $role->addChild("list_user_in_country");
    
    $bizRule = "return Yii::app()->user->isAdmin;";
    $role = $this->createRole("admin", "admin role", $bizRule);
    // 分配”操作“
    $role->addChild("list_all_user");
    $role->addChild("country_manager");
    
    $bizRule = "return Yii::app()->user->isAuthenticated;";
    $this->createRole("authen", "authenticated user", $bizRule);
    
    $bizRule = "return Yii::app()->user->isGuest;";
    $this->createRole("guest", "guest user", $bizRule);
  }
  
  
}
