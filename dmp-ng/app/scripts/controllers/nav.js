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


/*global angular */
(function(){
	'use strict';

	/**
	 * @ngdoc controller
	 * @name dmpApp.controller:NavCtrl
	 * @description
	 * # NavCtrl
	 * Controller of the dmpApp
	 * Used to display user name and list projects in navbar.
	 */
	angular.module('dmpApp')
	.controller('NavCtrl', NavCtrl);

	NavCtrl.$inject = ['Authentication','User', 'siteTitle', 'localStorageService', '$scope', '$filter'];

	function NavCtrl(Authentication, User, siteTitle, localStorageService, $scope, $filter){
		var vm = this;
		vm.projects = [];
		vm.setProject = setProject;
		vm.siteTitle = siteTitle.name;
		vm.currentYear = new Date().getFullYear();
		///////

	/**
	 * @ngdoc function
	 * @name dmpApp.controller:LoginCtrl:getUser
	 * @description
	 * # Get user details from backend based on username stored in Authentication service.
	 * # sets current project  = first project of the project list.
	 * Controller of the dmpApp
	 */
		function getUser(){
			vm.user = {username: '', firstname: '', lastname: '',project_id: null};
			if (Authentication.currentUser.username){
				User.GetByAuthdata(Authentication.currentUser.authdata).then(function(user){
					vm.user.username = user.login;
					vm.user.firstname = user.firstname;
					vm.user.lastname = user.lastname;
					vm.user.user_id = user.user_id;
				});
			}
		}

		getUser();


	/**
	 * @ngdoc function
	 * @name dmpApp.controller:LoginCtrl:setProject
	 * @param project_id: int. id of the new current project
	 * @description
	 * # Sets the new current project. Use the Authentication service.
	 * Controller of the dmpApp
	 */

		function setProject(project_id){
			// Authentication.setProject(project_id);
		}

		$scope.$watch(function(){return localStorageService.get('currentUser');},function(n,o){
			if (n != o){
				getUser();
			}
		},true);

	}
})();
