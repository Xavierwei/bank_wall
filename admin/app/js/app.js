'use strict';


// Declare app level module which depends on filters, and services
angular.module('SGWallAdmin', [
  'ui.bootstrap',
  'ngRoute',
  'SGWallAdmin.filters',
  'myApp.services',
  'myApp.directives',
  'SGWallAdmin.controllers'
]).
config(function($routeProvider,$httpProvider) {
    $routeProvider.when('/node', {templateUrl: 'tmp/node/list.html', controller: 'NodeCtrList'});
    $routeProvider.when('/node/post', {templateUrl: 'tmp/node/post.html', controller: 'NodeCtrPost'});
    $routeProvider.when('/node/edit/:nid', {templateUrl: 'tmp/node/edit.html', controller: 'NodeCtrEdit'});
    $routeProvider.when('/node/comment/:nid', {templateUrl: 'tmp/comment/list.html', controller: 'CommentCtrList'});
    $routeProvider.when('/user', {templateUrl: 'tmp/user/list.html', controller: 'UserCtrList'});
    $routeProvider.when('/user/login', {templateUrl: 'tmp/user/login.html', controller: 'UserCtrLogin'});
    $routeProvider.when('/user/edit/:uid', {templateUrl: 'tmp/user/edit.html', controller: 'UserCtrEdit'});
    $routeProvider.when('/comment', {templateUrl: 'tmp/comment/list.html', controller: 'CommentCtrList'});
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