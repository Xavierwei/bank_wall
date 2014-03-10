<?php
$outname = rand(0,9999).time().'.mp4';
$cmd = "ffmpeg -i 01_07_filtering.mov -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 {$outname} 2>&1";
$result = shell_exec($cmd);

print_r($result);
?>