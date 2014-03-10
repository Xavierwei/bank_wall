<?php

<<<<<<< Updated upstream

ffmpeg -i /Users/tony/Desktop/v918.mp4 -acodec aac -strict experimental -ac 2 /Users/tony/Desktop/v91822.mp4 -metadata rotate=0

// fix ie8 and ie9 not support animate feature
if( !$('html').hasClass('csstransforms3d') ){
js flexbox canvas canvastext no-webgl no-touch geolocation postmessage no-websqldatabase indexeddb hashchange history draganddrop websockets rgba hsla multiplebgs backgroundsize no-borderimage borderradius boxshadow textshadow opacity cssanimations csscolumns cssgradients no-cssreflections csstransforms csstransforms3d csstransitions fontface generatedcontent video audio localstorage sessionstorage webworkers applicationcache svg inlinesvg no-smil svgclippaths


cd /var/www/vhosts/polyardshanghai.com/httpdocs/IMG_2011.MOV

ffmpeg -i IMG_2011.MOV -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 -vf "transpose=1" -metadata:s:v:0 rotate=0 v918out.mp4


If the IE8 doesn't have flash player,  the video will play using windows media player, which not support click on


ffmpeg -i v918.mp4 -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 -metadata:s:v:0 rotate=0 v918_2.mp4


ffmpeg -i {$to} -vcodec libx264 -threads 2 -movflags +faststart -acodec aac -strict experimental -ac 2 {$rotate} {$newpath}


sftp://64.207.184.106//var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/3/6/v918.mp4

ffmpeg -i sample2.3gp -movflags +faststart -strict -2 -ar 44100 sample2.mp4

ffmpeg -i /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/2/19/v468.mp4 -vcodec libx264 -acodec aac -strict experimental -ac 2 -vf "transpose=2" /var/www/vhosts/polyardshanghai.com/httpdocs/sgwall/api/uploads/2014/2/19/v4682.mp4
=======
function ffmpeg_process_count() {
  $command = "ps -ef | grep -v grep | grep ffmpeg | wc -l";
  
  $descriptorspec = array(
      0 => array("pipe", "r"),
      1 => array("pipe", "w"),
      2 => array("file", "/dev/null", "w"),
  );

  $process = proc_open($command, $descriptorspec, $pipes);
  $can_be_convert = FALSE;
  if (is_resource($process)) {
    fclose($pipes[0]);

    $content = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $ret_value = proc_close($process);

    return intval(trim($content));
  }

  else {
    // 打开进程失败
    return FALSE;
  }
}

# Linux / Centos only
function cpu_core_count() {
  $command = "cat /proc/cpuinfo | grep -v grep | grep processor | wc -l";
  
  $descriptorspec = array(
      0 => array("pipe", "r"),
      1 => array("pipe", "w"),
      2 => array("file", "/dev/null", "w"),
  );

  $process = proc_open($command, $descriptorspec, $pipes);
  if (is_resource($process)) {
    fclose($pipes[0]);

    $content = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $ret_value = proc_close($process);

    return intval(trim($content));
  }

  else {
    // 打开进程失败
    return FALSE;
  }
}
>>>>>>> Stashed changes
