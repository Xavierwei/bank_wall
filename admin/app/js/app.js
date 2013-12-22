'use strict';


// Declare app level module which depends on filters, and services
angular.module('SGWallAdmin', [
  'ngRoute',
  'SGWallAdmin.filters',
  'myApp.services',
  'myApp.directives',
  'SGWallAdmin.controllers'
]).
config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/node', {templateUrl: 'tmp/node/list.html', controller: 'NodeCtrList'});
  $routeProvider.when('/node/edit/:nid', {templateUrl: 'tmp/node/edit.html', controller: 'NodeCtrEdit'});
  $routeProvider.when('/user', {templateUrl: 'tmp/user/list.html', controller: 'UserCtrList'});
  $routeProvider.otherwise({redirectTo: '/view1'});
}]);
