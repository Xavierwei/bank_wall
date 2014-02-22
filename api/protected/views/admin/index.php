
<body>

	<div class="header">
		<div class="logo"><img src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/img/logo.gif" /></div>
	</div>

	<div class="page-left">
		<ul class="menu" nav>
			<li><a href="#/node"><em class="glyphicon glyphicon-th"></em> Contents</a></li>
			<li><a href="#/comment"><em class="glyphicon glyphicon-edit"></em> Comments</a></li>

			<li><span><em class="glyphicon glyphicon-flag"></em> Reported</span>
				<ul>
					<li><a href="#/node/flagged">- Contents</a></li>
					<li><a href="#/comment/flagged">- Comments</a></li>
				</ul>
			</li>
		</ul>
	</div>

	<div class="page-right" ng-controller="CtrGlobal">
		<div ng-view></div>
	</div>

	<script>
		var apiToken = '<?php echo $token;?>';
	</script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/jquery-1.102.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/lib/angular/angular.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/lib/angular/angular-route.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/lib/angular/ui-bootstrap-0.9.0.min.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/lib/angular/ui-bootstrap-tpls-0.9.0.min.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/app.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/services.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/services/node.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/services/user.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/services/like.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/services/comment.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/services/flag.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/lib.controllers.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/controllers.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/controllers/node.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/controllers/user.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/controllers/comment.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/filters.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/admin_asset/js/directives.js"></script>
</body>