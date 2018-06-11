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


/*global angular*/


(function(){
	'use strict';

	/**
	* @ngdoc filter
	* @name dmpApp.filter:user
	* @function
	* @description
	* # user
	* Filter in the dmpApp.
	*/
	angular.module('dmpApp')
	.filter('getGroupFromPermission', getGroupFromPermission);


	//////////////////

  	getGroupFromPermission.$inject = ['Group'];
	function getGroupFromPermission(Group) {
		return function (permissions) {
			if (!angular.isArray(permissions)) return permissions;
			var groups = [];
			for(var i = 0; i < permissions.length; i++){
				if (permissions[i].substr(0,6) === 'group_'){
					groups.push({group_id: permissions[i].substr(6)});
				};
			}
			return groups;
		};
	}
})();
