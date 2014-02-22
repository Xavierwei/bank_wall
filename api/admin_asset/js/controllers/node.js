SGWallAdminController
    .controller('NodeCtrList', function($scope, $http, $modal, $log, $routeParams, NodeService, LikeService, FlagService, ASSET_FOLDER) {

		$scope.hideList = '';
        // Get node list by recent
        var params = {};
        $scope.filter.status = 'all';
        $scope.filter.country = {};
        $scope.filter.country.country_name = 'All Country';
		params.orderby = "datetime";
		params.pagenum = 50;
		params.token = apiToken;


		$scope.$watch('filter.type + filter.country_id + filter.status + filter.country.country_id', function() {
            params.type = $scope.filter.type;
			if($scope.filter.type == 'all') {
				delete params.type;
			}

            if($scope.filter.status != 'all') {
                params.status = $scope.filter.status;
            }
            else {
                delete params.status;
            }

            params.country_id = $scope.filter.country.country_id;

			if($scope.filter.status != undefined) {
				params.status = $scope.filter.status;
			}
			$scope.hideList = 'hide-list';
			NodeService.list(params, function(data){
				$scope.hideList = '';
				$scope.nodes = data;
			});
		});

        // Switch node status
        $scope.updateStatus = function(node, status) {

            var newNode = angular.copy(node);
			if(node.status == 0) {
				newNode.status = 1;
			}
			else {
				newNode.status = 0;
			}
			NodeService.update(newNode,function(data){
				node.status = newNode.status;
			});
        }


        $scope.search = function() {
            if($scope.filter.status != 'all') {
                params.status = $scope.filter.status;
            }
            else {
                delete params.status;
            }
            params.hashtag = $scope.filter.hashtag;
			params.email = $scope.filter.email;
            NodeService.list(params, function(data){
                $scope.nodes = data;
            });
        }


        $scope.filterCountry = function(country) {
            $scope.filter.country = country;
        }

		$scope.reset = function() {
			$scope.filter.type = 'all';
			$scope.filter.status = 'all';
			$scope.filter.hashtag = '';
			$scope.filter.hashtag = '';
			$scope.filter.country = {country_name:'All Country', country_id:''};
		}





        // Delete node
        $scope.delete = function(node) {
            var modalInstance = $modal.open({
                templateUrl: ASSET_FOLDER + 'tmp/dialog/delete.html',
                controller: ConfirmModalCtrl
            });
            modalInstance.result.then(function () {
                $scope.nodes.splice($scope.nodes.indexOf(node), 1);
                NodeService.delete(node);
                $log.info('Node deleted at: ' + new Date());
            }, function () {
                $log.info('Modal dismissed at: ' + new Date());
            });
        }

        // Like node - TODO: this is for testing
        $scope.like = function(node) {
            //if(!node.user_liked) {
                LikeService.post(node.nid, function(data){
                    node.user_liked = !node.user_liked;
                });
            //}
        }

        // Unlike node - TODO: this is for testing
        $scope.unlike = function(node) {
            if(node.user_liked) {
                LikeService.delete(node.nid, function(data){
                    node.user_liked = !node.user_liked;
                });
            }
        }

        // Flag node - TODO: this is for testing
        $scope.flag = function(node) {
            FlagService.post('node', node.nid, function(data){
            });
        }

        // Clean Flag node - TODO: this is for testing
        $scope.cleanFlag = function(node) {
            FlagService.delete('node', node.nid, function(data){
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


    .controller('NodeCtrFlagged', function($scope, $http, $modal, $log, NodeService, ASSET_FOLDER, LikeService, FlagService) {
        // Get node list by flagged
        NodeService.getFlaggedNodes(function(data){
            $scope.nodes = data;
        });

        // Switch node status
        $scope.updateStatus = function(node, status) {
			console.log(node.status);
			if(node.status == 0) {
				var modalInstance = $modal.open({
					templateUrl: ASSET_FOLDER + 'tmp/dialog/unflag.html',
					controller: ConfirmModalCtrl
				});
				modalInstance.result.then(function () {
					var newNode = angular.copy(node);
					if(node.status == 0) {
						newNode.status = 1;
					}
					else {
						newNode.status = 0;
					}
					NodeService.update(newNode,function(){
						node.status = newNode.status;
						node.flagcount = 0;
					});
				}, function () {
				});
			}
			else {
				var newNode = angular.copy(node);
				if(node.status == 0) {
					newNode.status = 1;
				}
				else {
					newNode.status = 0;
				}
				NodeService.update(newNode,function(){
					node.status = newNode.status;
				});
			}
        }

        // Delete node
        $scope.delete = function(node) {
            var modalInstance = $modal.open({
                templateUrl: 'tmp/dialog/delete.html',
                controller: ConfirmModalCtrl
            });
            modalInstance.result.then(function () {
                $scope.nodes.splice($scope.nodes.indexOf(node), 1);
                NodeService.delete(node);
                $log.info('Node deleted at: ' + new Date());
            }, function () {
                $log.info('Modal dismissed at: ' + new Date());
            });
        }

    })

    .controller('NodeCtrPost',
    function($scope, $http) {

        // Update node
        $scope.post = function(node) {
            $http.post('http://localhost:8888/bank_wall/api/index.php?r=user/login',{company_email:user.company_email, password:user.password},{headers:'object'})
                .success(function(data) {
                })
                .error(function() {
                });
        }
    })

    .controller('NodeCtrEdit',
    function($scope, $http, NodeService, $routeParams) {

        NodeService.getById($routeParams.nid, function(data){
            $scope.node = data;
        });

        // Update node
        $scope.update = function(node) {
            NodeService.update(node);
        }

        // Delete node
        $scope.delete = function(node) {
            alert(node.nid);
        }

    })

    .controller('NodeCtrNeighbor',
    function($scope, $http, NodeService, $routeParams) {

        NodeService.getNeighbor($routeParams.nid, function(data){
            $scope.node = data;
        });



    })
