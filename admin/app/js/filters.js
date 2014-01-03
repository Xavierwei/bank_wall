'use strict';

/* Filters */

angular.module('SGWallAdmin.filters', [])
    .filter('interpolate', ['version', function(version) {
    return function(text) {
        return String(text).replace(/\%VERSION\%/mg, version);
    }
    }])
    .filter('nodeStatus', function() {
        return function(input) {
            var output;
            switch(input) {
                case '1':
                    output = 'Published';
                    break;
                case '2':
                    output = 'Unpublished';
                    break;
                case '3':
                    output = 'Blocked';
                    break;
            }
            return output;
        }
    })
    .filter('thumbnail', function() {
        return function(input) {
            var output = input;
            if(input.indexOf('mp4') > 0) {
                output = input.replace('mp4','jpg');
            }
            return output;
        }
    });
