'use strict';

/* Controllers */

angular.module('SGWallAdmin.controllers', [])
    .controller('NodeCtrList', ['$scope', '$http',
        function($scope, $http) {
            // Get node list by recent
            $http.get('json/node/recent.json')
                .success(function(data) {
                    $scope.nodes = data;
                })
                .error(function() {
                    $scope.error = "加载失败";
                });

            // Switch node status
            $scope.switchStatus = function(node) {
                var _status;
                if(node.status == '1') {
                    _status = '2';
                } else {
                    _status = '1';
                }
                $http.post('json/node/photo.json',{'nid':node.nid, 'status':_status})
                    .success(function(data) {
                        node.status = _status;
                    })
                    .error(function() {

                    });
            }
    }])
    .controller('NodeCtrEdit', ['$scope', '$http',
        function($scope, $http) {
            $http.get('json/node/photo.json')
                .success(function(data) {
                    $scope.node = data;
                })
                .error(function() {
                });

            // Update node
            $scope.update = function(node) {
                alert(node.description);
            }

            // Delete node
            $scope.delete = function(node) {
                alert(node.nid);
            }

        }])
    .controller('UserCtrList', ['$scope', '$http',
        function($scope, $http) {
            $http.get('json/user/all.json')
                .success(function(data) {
                    $scope.users = data;
                })
                .error(function() {
                    $scope.error = "加载失败";
                });
            $scope.switchStatus = function(uid) {
                alert(uid);
            }
        }])
    .controller('UserCtrEdit', ['$scope', '$http', '$routeParams',
        function($scope, $http, $routeParams) {
            $http.get('json/user/user.json?uid=' + $routeParams.uid)
                .success(function(data) {
                    $scope.user = data;
                })
                .error(function() {
                });

            // Update node
            $scope.update = function(user) {
                alert(user.uid);
            }

            // Delete node
            $scope.delete = function(user) {
                alert(user.uid);
            }
        }])
    .controller('CommentCtrList', ['$scope', '$http', '$routeParams',
        function($scope, $http, $routeParams) {
            $http.get('json/comment/recent.json?nid=' + $routeParams.nid)
                .success(function(data) {
                    $scope.comments = data;
                })
                .error(function() {
                });

            // Update node
            $scope.update = function(comment) {
                alert(comment.cid);
            }

            // Delete node
            $scope.delete = function(comment) {
                alert(comment.cid);
            }
        }]);