SGWallAdminServices.factory( 'UserService', function($http, ROOT) {
    var currentUser, isLoggedin;
    return {
        login: function(user, success) {
            $http.post(ROOT+'/user/login',{company_email:user.company_email, password:user.password})
            .success(function(data) {
                currentUser = data.data;
                success();
            })
            .error(function() {
            });
        },

        logout: function() { },

        getCurrentUser: function(success) {
            if(!currentUser) {
                $http.get(ROOT+'/user/GetCurrent')
                .success(function(data) {
                    currentUser = data.data;
                    success(data.data);
                })
            } else {
                success(currentUser);
            }
        },

        getByUid: function(uid, success) {
            $http.get(ROOT+'/user/GetByUid?uid='+uid)
            .success(function(data) {
                success(data.data);
            })
        },

        list: function(success) {
            $http.get(ROOT+'/user/list?role=1&country_id=1&orderby=datetime')
            .success(function(data) {
                success(data.data);
            })
            .error(function() {

            });
        },

        save: function(user) {
            $http.post(ROOT+'/user/post',user)
            .success(function(data) {
                console.log(data);
            })
            .error(function() {

            });
        },

        update: function(user) {
            $http.post(ROOT+'/user/Userput',user)
            .success(function(data) {
                console.log(data);
            })
            .error(function() {

            });
        },

        delete: function(user) {
            $http.post(ROOT+'/user/delete',{uid:user.uid})
                .success(function(data) {
                    console.log(data);
                })
                .error(function() {

                });
        }
    };
});
