<?php
$I = new ApiGuy($scenario);
$I->wantTo('Post Photo');
$I->sendPost('/node/post', array(
    "description" => "I am from test",
    "photo" => "@/Applications/MAMP/htdocs/bank_wall/admin/app/photo/1.jpg"
));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('http error');
?>