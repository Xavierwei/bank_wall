'use strict';

/* Controllers */

angular.module('SGWallAdmin.controllers', [])
    .controller('NodeCtrList', function($scope, $http,$modal,$log) {
            // Get node list by recent
            $http.get('json/node/recent.json')
                .success(function(data) {
                    $scope.nodes = data.data;
                })
                .error(function() {
                    $scope.error = "加载失败";
                });

            // Switch node status
            $scope.updateStatus = function(node, status) {
                $http.post('json/node/photo.json',{'nid':node.nid, 'status':status})
                    .success(function(data) {
                        node.status = status;
                    })
                    .error(function() {

                    });
            }

            // Delete node
            $scope.delete = function(node) {
                var modalInstance = $modal.open({
                    templateUrl: 'tmp/dialog/delete.html',
                    controller: ConfirmModalCtrl
                });

                modalInstance.result.then(function () {
                    $scope.nodes.splice($scope.nodes.indexOf(node), 1);
                    $log.info('Modal confirmed at: ' + new Date());
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });

            }


            $scope.open = function(node) {
                var modalInstance = $modal.open({
                    templateUrl: 'tmp/node/popup.html',
                    controller: NodeModalCtrl,
                    resolve: {
                        node: function () {
                            return node;
                        }
                    }
                });

                modalInstance.result.then(function () {
                    $scope.nodes.splice($scope.nodes.indexOf(node), 1);
                    $log.info('Modal confirmed at: ' + new Date());
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            }

            // Next
            $scope.next = function() {
                $http.get('json/node/recent2.json')
                    .success(function(data) {
                        $scope.nodes = data.data;
                    })
                    .error(function() {
                        $scope.error = "加载失败";
                    });
            }
    })


    .controller('NodeCtrEdit',
        function($scope, $http) {
            $http.get('json/node/photo.json')
                .success(function(data) {
                    $scope.node = data.data;
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

        })


    .controller('UserCtrList',
        function($scope, $http) {
            $http.get('json/user/all.json')
                .success(function(data) {
                    $scope.users = data.data;
                })
                .error(function() {
                    $scope.error = "加载失败";
                });
            $scope.switchStatus = function(uid) {
                alert(uid);
            }
        })


    .controller('UserCtrEdit',
        function($scope, $http, $routeParams) {
            $http.get('json/user/user.json?uid=' + $routeParams.uid)
                .success(function(data) {
                    $scope.user = data.data;
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
        })


    .controller('CommentCtrList',
        function($scope, $http) {
            $http.get('json/comment/recent.json')
                .success(function(data) {
                    $scope.comments = data.data;
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
        });




var NodeModalCtrl = function($scope, $modalInstance, node) {
    $scope.node = node;

    $scope.ok = function () {
        $modalInstance.close(true);
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}



