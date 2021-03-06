<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';
// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',5);

define("ROOT", dirname(__FILE__));
define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT']);
$simplesamlphp = ROOT."/simplesamlphp";
include_once( $simplesamlphp.'/lib/_autoload.php' );

require_once($yii);
Yii::createWebApplication($config)->run();
