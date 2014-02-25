<?php
	$video = $_GET['file'];
  // Get video thumbnail ratio, use to resize the wmv video
	$cover = str_replace('wmv','jpg',$video);
  $size = getimagesize("./api/".$cover);
  $ratio = $size[0] / $size[1];
?>
<!DOCTYPE>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <title>SG WALL</title>
    <link href="css/layout.css" rel="stylesheet" type="text/css" />
    <link href="css/animation.css" rel="stylesheet" type="text/css" />
    <link href="css/fonts.css" rel="stylesheet" type="text/css" />
    <style>
        html,body {height:100%;width:100%;text-align: center;}
    </style>
</head>
<body>
<object id="player" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" standby="Loading Microsoft® Windows® Media Player components..." width="600" height="600" type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsm p2inf.cab#Version=6,4,7,1112">
    <param name="fileName" value="./api<?php echo $video;?>">
    <param name="animationatstart" value="false">
    <param name="transparentatstart" value="false">
    <param name="autostart" value="true">
    <param name="showcontrols" value="false">
	<param name="BufferingTime" value="5">
	<param name="fullScreen" value="true">
	<param name="ShowStatusBar" value="false">
    <param name="windowlessvideo" value="true">
    <param name="AllowChangeDisplaySize" value="true">
    <param name="StretchToFit" value="false">
    <param name="AutoSize" value="false">
    <param name="DisplaySize" value="1">
    <param name="Rate" value="1.0">
</object>

<div class="click">click</div>
<script src="js/jquery/jquery-1.102.js"></script>
<script>
  var _resizeTimer = null;
  $(window).resize(function(){
    clearTimeout( _resizeTimer );
    _resizeTimer = setTimeout(function(){
      var ratio = <?php echo $ratio;?>;
			var windowRatio = $(window).width()/$(window).height();
			if(ratio > windowRatio) {
				var width = $(window).width();
				var height = parseInt(width / ratio);
				var marginTop = ($(window).height() - height)/2;
			}
			else {
				var height = $(window).height();
				var width = parseInt(height * ratio);
				var marginTop = 0;
			}
      $('#player').width(width).height(height).css({'margin-top':marginTop});
    }, 500);
  }).trigger('resize');

  $('.click').click(function(){
	  var Wmp = document.getElementById("player");
	  if (Wmp.controls.isAvailable('Stop'))
		  Wmp.controls.stop();
  });


</script>
</body>
</html>


