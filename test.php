<?php
//1389527410   195
//1389491072   122
$datetime = date('Y-m-d','1392175200');
$datetime2 = date(r,'1392175200');
$start_time = strtotime($datetime);
echo $datetime2 .'<br/>'.$datetime.'<br/>';
echo $start_time;
?>