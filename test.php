<?php
//1389527410   195
//1389491072   122
$datetime = date('Y-m-d','1392175200');
$datetime2 = date(r,'1392175200');
$start_time = strtotime($datetime);
echo $datetime2 .'<br/>'.$datetime.'<br/>';
echo $start_time;
?>
ffmpeg -i /Users/tony/Desktop/VID_20140301_180246.mp4 -vcodec libx264 -filter:v scale=720:-1 -acodec aac -strict experimental -ac 2 /Users/tony/Desktop/VID_20140301_1802462.mp4


ffmpeg -i /Users/tony/Desktop/v918.mp4 -acodec aac -strict experimental -ac 2 /Users/tony/Desktop/v91822.mp4 -metadata rotate=0

// fix ie8 and ie9 not support animate feature
if( !$('html').hasClass('csstransforms3d') ){
js flexbox canvas canvastext no-webgl no-touch geolocation postmessage no-websqldatabase indexeddb hashchange history draganddrop websockets rgba hsla multiplebgs backgroundsize no-borderimage borderradius boxshadow textshadow opacity cssanimations csscolumns cssgradients no-cssreflections csstransforms csstransforms3d csstransitions fontface generatedcontent video audio localstorage sessionstorage webworkers applicationcache svg inlinesvg no-smil svgclippaths


cd /var/www/vhosts/polyardshanghai.com/httpdocs/IMG_2011.MOV

ffmpeg -i IMG_2011.MOV -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 -vf "transpose=1" -metadata:s:v:0 rotate=0 v918out.mp4


If the IE8 doesn't have flash player,  the video will play using windows media player, which not support click on


ffmpeg -i v918.mp4 -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 -metadata:s:v:0 rotate=0 v918_2.mp4



sftp://64.207.184.106//var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/3/6/v918.mp4

ffmpeg -i /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/2/19/v468.mp4 -vcodec libx264 -acodec aac -strict experimental -ac 2 -vf "transpose=2" /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/2/19/v4682.mp4