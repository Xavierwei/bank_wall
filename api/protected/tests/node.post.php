<?php

$http = "http://bankapi.local/index.php/node/post";
$req = curl_init();

curl_setopt($req, CURLOPT_POST, TRUE);
curl_setopt($req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($req, CURLOPT_URL, $http);
curl_setopt($req, CURLOPT_POSTFIELDS, array(
    "description" => "I am at #Starbar and work for #Shanghai Company",
    //"photo" => "@/home/jackey/Pictures/afb3ddea748c1e40674cec9ec62c0245.gif;type=image/gif",
    "video" => "@/home/jackey/Videos/t3.avi"
));

// 发送cookie
$cookie = "PHPSESSID=befg5lru99n1m9s4tlt8kjoip1";
curl_setopt($req, CURLOPT_COOKIE, $cookie);

//curl_setopt($req, CURLOPT_HTTPHEADER, array("Content-Type: image/png"));
$res = curl_exec($req);

echo $res;

