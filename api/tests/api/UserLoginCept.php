<?php
$I = new ApiGuy($scenario);
$I->wantTo('User Login');
$I->sendPost('user/login',array(
    "company_email" => "jziwenchen@gmail.com",
    "password" => "admin"
));
$I->seeResponseIsJson();
$I->seeResponseContains('jziwenchen1@gmail.com');
?>