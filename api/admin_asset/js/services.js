'use strict';

/* Services */


// Demonstrate how to register services
// In this case it is a simple value service.
var SGWallAdminServices = angular.module('SGWallAdmin.services', [])
    .value('ROOT', '..')
    .value('ROOT_FOLDER', '..')
	.value('ASSET_FOLDER', '../admin_asset/');
