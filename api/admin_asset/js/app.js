'use strict';


// Declare app level module which depends on filters, and services
var SGWallAdmin = angular.module('SGWallAdmin', [
  'ui.bootstrap',
  'ngRoute',
  'SGWallAdmin.filters',
  'SGWallAdmin.services',
  'myApp.directives',
  'SGWallAdmin.controllers'
]).
config(function($routeProvider,$httpProvider) {
	var ROOT = '../admin_asset/';
    $routeProvider.when('/node', {templateUrl: ROOT +'tmp/node/list.html', controller: 'NodeCtrList'});
    $routeProvider.when('/node/list/:type', {templateUrl: ROOT +'tmp/node/list.html', controller: 'NodeCtrList'});
    $routeProvider.when('/node/flagged', {templateUrl: ROOT +'tmp/node/list.html', controller: 'NodeCtrFlagged'});
    $routeProvider.when('/node/neighbor/:nid', {templateUrl: ROOT +'tmp/node/neighbor.html', controller: 'NodeCtrNeighbor'});
    $routeProvider.when('/node/post', {templateUrl: ROOT +'tmp/node/post.html', controller: 'NodeCtrPost'});
    $routeProvider.when('/node/edit/:nid', {templateUrl: ROOT +'tmp/node/edit.html', controller: 'NodeCtrEdit'});
    $routeProvider.when('/node/comment/:nid', {templateUrl: ROOT +'tmp/comment/list.html', controller: 'CommentCtrList'});
    $routeProvider.when('/user', {templateUrl: ROOT +'tmp/user/list.html', controller: 'UserCtrList'});
    $routeProvider.when('/user/login', {templateUrl: ROOT +'tmp/user/login.html', controller: 'UserCtrLogin'});
    $routeProvider.when('/user/logout', {templateUrl: ROOT +'tmp/user/login.html', controller: 'UserCtrLogout'});
    $routeProvider.when('/user/current', {templateUrl: ROOT +'tmp/user/current.html', controller: 'UserCtrCurrent'});
    $routeProvider.when('/user/create', {templateUrl: ROOT +'tmp/user/create.html', controller: 'UserCtrCreate'});
    $routeProvider.when('/user/edit/:uid', {templateUrl: ROOT +'tmp/user/edit.html', controller: 'UserCtrEdit'});
    $routeProvider.when('/comment', {templateUrl: ROOT +'tmp/comment/list.html', controller: 'CommentCtrList'});
    $routeProvider.when('/comment/flagged', {templateUrl: ROOT +'tmp/comment/list.html', controller: 'CommentCtrFlagged'});
    $routeProvider.when('/comment/post/:nid', {templateUrl: ROOT +'tmp/comment/post.html', controller: 'CommentCtrPost'});
    $routeProvider.when('/comment/edit/:cid', {templateUrl: ROOT +'tmp/comment/edit.html', controller: 'CommentCtrEdit'});
    $routeProvider.otherwise({redirectTo: '/node'});


    $httpProvider.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
    $httpProvider.defaults.transformRequest = [function(data)
    {
        var param = function(obj)
        {
            var query = '';
            var name, value, fullSubName, subName, subValue, innerObj, i;

            for(name in obj)
            {
                value = obj[name];

                if(value instanceof Array)
                {
                    for(i=0; i<value.length; ++i)
                    {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value instanceof Object)
                {
                    for(subName in value)
                    {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value !== undefined && value !== null)
                {
                    query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
                }
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };

        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
});

