<?php
$I = new ApiGuy($scenario);
$I->wantTo('Get Node List');
$I->sendGet('node/list&type=photo');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('http error');
?>