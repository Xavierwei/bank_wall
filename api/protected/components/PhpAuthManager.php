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
    
    $this->createOperation("deleteNode", "delete one node");
    $this->createOperation("blockNode", "block one node");
    $this->createOperation("unpublishNode", "unpublish one node");
    $this->createOperation("publichsNode", "publish one node");
    
    $this->createOperation("updateNode", "update one node");
    $this->createOperation("updateAnyNode", "update any node");
    
    $bizRule = 'return Yii::app()->user->id == $params["post"]->uid';
    $task = $this->createTask("updateOwnNode", "update own node", $bizRule);
    $task->addChild("updateNode");
    
    $admin = $this->createRole("admin");
    $admin->addChild("deleteNode");
    $admin->addChild("blockNode");
    $admin->addChild("unpublishNode");
    $admin->addChild("updateNode");
    
    $uid = Yii::app()->user->id;
    
    if ($uid) {
      // 设置权限
    }
    
  }
  
}
