'use strict';

/* Services */


// Demonstrate how to register services
// In this case it is a simple value service.
var SGWallAdminServices = angular.module('SGWallAdmin.services', []).
  value('ROOT', 'http://localhost/bank_wall/api/index.php');
