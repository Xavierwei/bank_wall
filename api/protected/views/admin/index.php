
<body>


<div class="page-left">
	<ul class="menu">
		<li><a href="#/node">Contents</a>
			<!--<ul>-->
			<!--<li><a href="#/node">Photos</a></li>-->
			<!--<li><a href="#/node">Videos</a></li>-->
			<!--</ul>-->
		</li>
		<li><a href="#/node/post">Post node</a></li>
		<li><a href="#/comment">Comments</a></li>
		<!--<li><a href="#/like">Likes</a></li>-->
		<li><a href="#/user">Users</a>
			<ul>
				<li><a href="#/user/login">Login</a></li>
				<li><a href="#/user/current">Get Current User</a></li>
				<li><a href="#/user/create">Create User</a></li>
				<li><a href="#/user/logout">Logout</a></li>
			</ul>
		</li>

		<li>Non-appropriate
			<ul>
				<li><a href="#/node/flagged">Contents</a></li>
				<li><a href="#/comment/flagged">Comments</a></li>
			</ul>
		</li>
	</ul>
</div>

<div class="page-right" ng-controller="CtrGlobal">
	<div ng-view></div>
</div>

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