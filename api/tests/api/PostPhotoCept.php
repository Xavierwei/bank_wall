<?php
$I = new ApiGuy($scenario);
$I->wantTo('Post Photo');
$I->sendPost('node/post', array(
    "description" => "I am from test",
    "photo" => "@/home/jackey/Pictures/test.png"
));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('http error');
?>