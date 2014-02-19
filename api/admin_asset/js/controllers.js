'use strict';

/* Controllers */

var SGWallAdminController = angular.module('SGWallAdmin.controllers', []);

SGWallAdminController
	.controller('CtrGlobal', function($scope, $http, $modal, $log, $routeParams, NodeService, LikeService, FlagService, ASSET_FOLDER) {
		$scope.filter = {};
		$scope.filter.type = 'all';
	})