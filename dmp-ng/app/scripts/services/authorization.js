/************************ LICENCE ***************************
*     This file is part of <DMP Canvas Generator web application>
*     Copyright (C) <2016> SIB Swiss Institute of Bioinformatics
*
*     This program is free software: you can redistribute it and/or modify
*     it under the terms of the GNU Affero General Public License as
*     published by the Free Software Foundation, either version 3 of the
*     License, or (at your option) any later version.
*
*     This program is distributed in the hope that it will be useful,
*     but WITHOUT ANY WARRANTY; without even the implied warranty of
*     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*     GNU Affero General Public License for more details.
*
*     You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>
*
*****************************************************************/


/*jslint node: true, newcap: true */
/*global angular */

(function(){
	'use strict';

	/**
	* @ngdoc service
	* @name dmpApp.authorization
	* @description
	* # authorization
	* Factory in the dmpApp.
	*/
	angular.module('dmpApp')
	.factory('Authorization', Authorization);

	Authorization.$inject = ['Authentication'];


	function Authorization(Authentication) {

		var service = {};

		service.authorize = authorize;


		return service;

		function authorize(loginRequired, requiredPermissions, permissionCheckType){

			var result = true,
			user = Authentication.getCredentials(),
			loweredPermissions = [],
			hasPermission = true,
			permission, i;

			permissionCheckType = permissionCheckType || "one";



			if (loginRequired === true && user.username === undefined) {
				result = false;
			}

			else if ((loginRequired === true && user.username !== undefined) &&
			(requiredPermissions === undefined || requiredPermissions.length === 0)) {
				result = true;



			}

			else if (requiredPermissions) {
				loweredPermissions = [];
				angular.forEach(user.permissions, function (permission) {
					loweredPermissions.push(permission.toLowerCase());
				});

				for (i = 0; i < requiredPermissions.length; i += 1) {
					permission = requiredPermissions[i].toLowerCase();
					var negative = permission.substr(0,1) === '!';
					if (negative) permission = permission.substr(1);
					if (permissionCheckType === 'all') {
						if (negative) hasPermission = hasPermission && loweredPermissions.indexOf(permission) === -1;
						else hasPermission = hasPermission && loweredPermissions.indexOf(permission) > -1;
						// if all the permissions are required and hasPermission is false there is no point carrying on
						if (hasPermission === false) {
							break;
						}
					} else if (permissionCheckType === 'one') {
						if (negative) hasPermission = loweredPermissions.indexOf(permission) === -1;
						else hasPermission = loweredPermissions.indexOf(permission) > -1;
						// if we only need one of the permissions and we have it there is no point carrying on
						if (hasPermission) {
							break;
						}
					}
				}

				result = hasPermission;
			}
			return result;
		}

	}
})();
