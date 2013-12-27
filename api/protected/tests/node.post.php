<?php

$http = "http://bankapi.local/index.php/node/post";
$req = curl_init();

curl_setopt($req, CURLOPT_POST, TRUE);
curl_setopt($req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($req, CURLOPT_URL, $http);
curl_setopt($req, CURLOPT_POSTFIELDS, array(
    "description" => "I am at #Starbar and work for #Shanghai Company",
    //"photo" => "@/home/jackey/Pictures/user_register.png;type=image/png",
    "video" => "@/home/jackey/Videos/mpeg.mp4"
));

//curl_setopt($req, CURLOPT_HTTPHEADER, array("Content-Type: image/png"));
$res = curl_exec($req);

echo $res;

