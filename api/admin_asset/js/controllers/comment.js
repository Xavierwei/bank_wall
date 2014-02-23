SGWallAdminController
    .controller('CommentCtrList', function($scope, CommentService, $modal, $log, FlagService) {

		$scope.hideList = '';
		$scope.noResult = false;
        var params = {};
        $scope.filter.status = 'all';
		$scope.page = 1;
        params.shownode = true;
        params.showall = true;
        params.order = 'DESC';
		params.pagenum = 16;


        $scope.$watch('filter.status', function() {
            if($scope.filter.status != 'all') {
                params.status = $scope.filter.status;
                delete params.showall;
            }
            else {
                params.showall = true;
                delete params.status;
            }

			$scope.page = 1;
			params.page = $scope.page;
			loadComment(params);
        });

		$scope.$watch('page', function() {
			params.page = $scope.page;
			if($scope.page == 1) {
				$scope.first = true;
			}
			loadComment(params);
		});

        // Switch node status
        $scope.updateStatus = function(comment) {
            var newComment = angular.copy(comment);
            if(comment.status == 0) {
                newComment.status = 1;
            }
            else {
                newComment.status = 0;
            }
            CommentService.update(comment,function(data){
                comment.status = newComment.status;
            });
        }


        // Update node
        $scope.update = function(comment) {
            alert(comment.cid);
        }

		$scope.nextPage = function() {
			$scope.page ++;
			$scope.first = false;
		}

		$scope.prevPage = function() {
			$scope.page --;
			$scope.end = false;
		}


		function loadComment(params) {
			$scope.hideList = 'hide-list';
			CommentService.list(params, function(data){
				$scope.hideList = '';
				if(data.length == 0) {
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
				$scope.comments = data;
			});
		}
    })

    .controller('CommentCtrFlagged', function($scope, CommentService, $modal, $log, FlagService) {
        CommentService.getFlaggedComments(function(data) {
            $scope.comments = data;
        });

        // Update node
        $scope.update = function(comment) {
            alert(comment.cid);
        }
    })


