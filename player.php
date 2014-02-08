<?php
  // Get video thumbnail ratio, use to resize the wmv video
  $size = getimagesize("./api/uploads/v199.jpg");
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
        html,body {height:100%;width:100%;}
    </style>
</head>
<body>
<object id="player" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" standby="Loading Microsoft® Windows® Media Player components..." width="600" height="600" type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsm p2inf.cab#Version=6,4,7,1112">
    <param name="fileName" value="./api/uploads/v199.wmv">
    <param name="animationatstart" value="false">
    <param name="transparentatstart" value="false">
    <param name="autostart" value="true">
    <param name="showcontrols" value="true">
    <param name="ShowStatusBar" value="true">
    <param name="windowlessvideo" value="true">
    <param name="AllowChangeDisplaySize" value="true">
    <param name="StretchToFit" value="false">
    <param name="AutoSize" value="false">
    <param name="DisplaySize" value="1">
    <param name="Rate" value="1.0">
</object>

<script src="js/jquery/jquery-1.102.js"></script>
<script>
  var _resizeTimer = null;
  $(window).resize(function(){
    clearTimeout( _resizeTimer );
    _resizeTimer = setTimeout(function(){
      var ratio = <?php echo $ratio;?>;
      var width = $(window).width();
      var height = parseInt(width / ratio);
      $('#player').width(width).height(height);
    }, 500);
  }).trigger('resize');
</script>
</body>
</html>


