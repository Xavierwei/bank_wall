<?php
$I = new ApiGuy($scenario);
$I->wantTo('Post Country');
$I->sendPost('country/post',array(
    "country_name" => "China",
    "code" => "cn",
    "flag_icon"=> "flag",
));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('http error');
?>
