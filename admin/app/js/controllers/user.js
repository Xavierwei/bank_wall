SGWallAdminController
    .controller('UserCtrList',
    function($scope, UserService) {
        UserService.list(function(data){
            $scope.users = data;
        });

        $scope.switchStatus = function(uid) {
            alert(uid);
        }
    })


    .controller('UserCtrEdit',
    function($scope, $http, $routeParams, UserService) {
        UserService.getByUid($routeParams.uid, function(data) {
            $scope.user = data;
        });

        // Update node
        $scope.update = function(user) {
            UserService.update(user, function(data) {
                console.log(data);
            });
        }

        // Delete node
        $scope.delete = function(user) {
            UserService.delete(user, function(data) {
                console.log(data);
            });
        }
    })

    .controller('UserCtrCreate',
    function($scope, UserService) {
        // save user
        $scope.save = function(user) {
            UserService.save(user);
        }
    })

    .controller('UserCtrCurrent',
    function($scope, UserService) {
        // get current user
        UserService.getCurrentUser(function(data){
            $scope.user = data;
        });
    })

    .controller('UserCtrLogin',
    function($scope, UserService) {
        // login
        $scope.login = function(user) {
            UserService.login(user);
        }
    })

    .controller('UserCtrInfo',
    function($scope, $http, UserService) {
        // login
        $scope.user = UserService.info();
    })