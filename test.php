<?php
//1389527410   195
//1389491072   122
$datetime = date('Y-m-d','1392175200');
$datetime2 = date(r,'1392175200');
$start_time = strtotime($datetime);
echo $datetime2 .'<br/>'.$datetime.'<br/>';
echo $start_time;
?>



ffmpeg -i /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/2/19/v468.mp4 -vcodec libx264 -acodec aac -strict experimental -ac 2 -vf "transpose=2" /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/2/19/v4682.mp4