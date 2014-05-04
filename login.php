<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <title>L'ESPRIT D'EQUIPE SOCIETE GENERALE</title>
    <link href="css/layout.css" rel="stylesheet" type="text/css" />
	<link href="css/fonts.css" rel="stylesheet" type="text/css" />
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-49965227-1', 'wall150ans.com');
		ga('send', 'pageview');

	</script>
</head>
<body class="login-wrap">
<div class="header">
	<a class="logo" href="./"></a>
	<div class="language">
		<div data-a="lang" data-d="lang=fr" class="btn language-item language-item-fr"><p class="fr"></p></div>
		<div data-a="lang" data-d="lang=en" class="btn language-item language-item-en"><p class="en"></p></div>
	</div>
</div>
<div class="login-box">
	<?php if(isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'en'):?>
		<div class="login-term">
			By logging into this website,
			<a target="_blank" href="pdf/wall150ans_cgu_en.pdf">you accept the terms of use of this website.</a>
		</div>
		<div class="login-btns">
			<a href="./">BACK</a>
			<a href="./api/user/samllogin">I AGREE</a>
		</div>
	<?else:?>
		<div class="login-term">
			En vous connectant à ce site web,
			<a target="_blank" href="pdf/wall150ans_cgu_fr.pdf">vous acceptez les conditions d'utilisations de ce site.</a>
		</div>
		<div class="login-btns">
			<a href="./">retour</a>
			<a href="./api/user/samllogin">J'ACCEPTE ET JE ME CONNECTE</a>
		</div>
	<?endif;?>
</div>

<script type="text/javascript" src="./js/plugin/modernizr-2.5.3.min.js"></script>
<script type="text/javascript" src="./js/sea/sea-debug.js" data-config="../config.js"></script>
<script type="text/javascript" src="./js/sea/plugin-shim.js"></script>
<script type="text/javascript" src="./js/lp.core.js"></script>
<script type="text/javascript" src="./js/lp.login.js"></script>
<!--[if IE 8]>
<script type="text/javascript" src="./js/ie8.js"></script>
<!--<![endif]-->
</body>
</html>