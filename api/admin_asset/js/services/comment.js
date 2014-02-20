SGWallAdminServices.factory( 'CommentService', function($http, ROOT) {
    return {
        list: function(param, success) {
            $http.get(ROOT+'/comment/list', {
                params: param,
                cache: false
            })
            .success(function(data) {
                success(data.data);
            })
            .error(function() {
            });
        },

        getFlaggedComments: function(success) {
            $http.get(ROOT+'/flag/getFlaggedComments')
            .success(function(data) {
                success(data.data);
            })
            .error(function() {
            });
        },

        getById: function(cid, success) {
            $http.get(ROOT+'/comment/getById?cid='+cid)
            .success(function(data) {
                success(data.data);
            })
            .error(function() {
            });
        },

        post: function(comment, success) {
            $http.post(ROOT+'/comment/post',comment)
            .success(function(data) {
                success(data);
            })
            .error(function() {

            });
        },

        update: function(comment, success) {
            $http.post(ROOT+'/comment/put',comment)
            .success(function(data) {
                if(data.success == true) {
                    success(data);
                }
            })
            .error(function() {

            });
        },

        delete: function(comment) {
            $http.post(ROOT+'/comment/delete',{cid:comment.cid})
            .success(function(data) {
                console.log(data);
            })
            .error(function() {

            });
        }
    };
});
