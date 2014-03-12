<?php

$http = "http://bankwall.local/node/post";
error_reporting(E_ALL);

$req = curl_init();

curl_setopt($req, CURLOPT_POST, TRUE);
curl_setopt($req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($req, CURLOPT_URL, $http);
curl_setopt($req, CURLOPT_VERBOSE, true);
curl_setopt($req, CURLOPT_POSTFIELDS, array(
    "description" => "I am at #Starbar and work for #Shanghai Company",
    //"photo" => "@/Applications/MAMP/htdocs/bank_wall/web/photo/1.jpg;type=image/jpg",
    "tmp_file" => "/uploads/2014/3/12/349caf35d3f797f8809e1b8a3670ae4a.avi",
    "iframe" => TRUE,
));

//curl_setopt($req, CURLOPT_HTTPHEADER, array("Content-Type: image/png"));
$res = curl_exec($req);

print_r("res: ".$res);

