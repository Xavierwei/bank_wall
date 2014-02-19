<!doctype html>
<html lang="en" ng-app="SGWallAdmin">
<head>
  <meta charset="utf-8">
  <title>SG Admin</title>
  <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/css/admin.css?<?php echo time();?>"/>
</head>

<?php echo $content; ?>

</html>
