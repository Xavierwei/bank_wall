<?php
$I = new ApiGuy($scenario);
$I->wantTo('Get User List');
$I->sendGet('user/list');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('http error');
?>