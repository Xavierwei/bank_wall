<?php
$I = new ApiGuy($scenario);
$I->wantTo('Post Photo');
$I->sendPost('node/post');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('http error');
?>