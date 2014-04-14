<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
	<meta name="viewport" content="minimal-ui, width=640, minimum-scale=0.5, maximum-scale=0.5, target-densityDpi=290,user-scalable=no" />
    <title>WALL - SOCIÉTÉ GÉNÉRALE</title>
    <link href="../css/jsPane.css" rel="stylesheet" type="text/css" />
    <link href="css/layout.css" rel="stylesheet" type="text/css" />
	<link href="css/photoitem.css" rel="stylesheet" type="text/css" />
    <link href="css/fonts.css" rel="stylesheet" type="text/css" />
    <link href="css/animation.css" rel="stylesheet" type="text/css" />
	<style>
		body {background: #fff;}
	</style>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-49965227-1', 'wall150ans.com');
		ga('send', 'pageview');

	</script>
</head>
<body>
<div class="turn_device"></div>
<div class="iphone_fixed"></div>
<div class="preload">
    <div class="preload1">a</div>
    <div class="preload2">a</div>
    <div class="preload3">a</div>
</div>
<div class="page-loading">
	<div class="page-loading-logo">
		<?php if(isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'en'):?>
			<img src="./img/home_logo_en.gif" />
		<?else:?>
			<img src="./img/home_logo.gif" />
		<?endif;?>
	</div>
</div>
<img id="imgLoad" />
<?php
	include('../include/tpl_mobile.php');
?>

<script type="text/javascript" src="../js/plugin/modernizr-2.5.3.min.js"></script>
<script type="text/javascript" src="../js/sea/sea-debug.js" data-config="../config-m"></script>
<script type="text/javascript" src="../js/sea/plugin-shim.js"></script>
<script type="text/javascript" src="../js/lp.core.js"></script>
<script type="text/javascript" src="../js/lp.base-m.js"></script>
</body>
</html>