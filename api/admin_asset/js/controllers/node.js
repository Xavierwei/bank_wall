SGWallAdminController
    .controller('NodeCtrList', function($scope, $http, $modal, $log, $routeParams, NodeService, LikeService, FlagService, ASSET_FOLDER) {

		$scope.hideList = '';
		$scope.noResult = false;
        // Get node list by recent
        var params = {};
        $scope.filter.status = 'all';
        $scope.filter.country = {};
        $scope.filter.country.country_name = 'All Country';
		$scope.page = 1;
		params.orderby = "datetime";
		params.pagenum = 20;
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

			$scope.page = 1;
			params.page = $scope.page;
			loadNodes(params);
		});


		$scope.$watch('page', function() {
			params.page = $scope.page;
			if($scope.page == 1) {
				$scope.first = true;
			}
			loadNodes(params);
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
			$scope.page = 1;
			params.page = $scope.page;
			loadNodes(params);
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


		$scope.nextPage = function() {
			$scope.page ++;
			$scope.first = false;
		}

		$scope.prevPage = function() {
			$scope.page --;
			$scope.end = false;
		}



		function loadNodes(params) {
			$scope.hideList = 'hide-list';
			NodeService.list(params, function(data){
				$scope.hideList = '';
				if (data.length == 0 && $scope.page == 1) {
					$scope.noResult = true;
				}
				else {
					$scope.noResult = false;
				}
				if(data.length < params.pagenum) {
					$scope.end = true;
				}
				else {
					$scope.end = false;
				}
				$scope.nodes = data;
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


    })

