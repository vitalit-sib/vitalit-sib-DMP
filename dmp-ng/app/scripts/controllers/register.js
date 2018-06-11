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

(function () {
	'use strict';

	/**
	* @ngdoc controller
	* @name dmpApp.controller:RegisterCtrl
	* @description
	* # RegisterCtrl
	* Controller of the dmpApp
	* send to backend to create an account. If successfull, set default http Authorization header for future connections. Set Authentication credentials.
	*
	*/


	angular
	.module('dmpApp')
	.controller('RegisterCtrl', RegisterController);

	RegisterController.$inject = ['User','Authentication', '$location', '$rootScope', 'toastr', '$routeParams', 'Base64', '$http', 'siteTitle'];
	function RegisterController(User, Authentication, $location, $rootScope, toastr, $routeParams, Base64, $http, siteTitle) {
		var vm = this;
		vm.siteTitle = siteTitle.name;
		vm.register = register;
		// vm.groups = Groups;

		var action = $location.path().split("/")[1];

		Authentication.clearCredentials();

		if (action == 'activation' || action == 'reject' ){
			var params = Base64.decode($routeParams.param).split(":");

			var user_id = params[0];
			var code = params[1];
			var leader_login = params[2];
			var leader_code = params[3];

			vm.message = {
				type: 'info',
				text: ''
			};

			if (leader_login){
				var authdata = Base64.encode(leader_login+":"+leader_code);
				$http.defaults.headers.common['Authorization'] = 'Basic ' + authdata; // jshint ignore:line
			}

			// Activate or Reject an account.

			User.GetById(user_id).then(function(user){
				if (user_id !== user.user_id || code !== user.activation_code || leader_login !== user.leader.login){
					vm.message.type = 'error';
					vm.message.text = 'Sorry, activation code is not valid';
				} else if (user.is_active == 'Y'){
					vm.message.type = 'warning';
					vm.message.text = 'User '+user.firstname+' '+user.lastname+' is already active';
				} else if (action == 'activation'){
					user.is_active = 'Y';
					User.Update(user).then(function(){
						vm.message.type = 'success';
						vm.message.text = 'The account for '+user.firstname+' '+user.lastname+' has been activated successfully';
					});
				} else if (action == 'reject'){
					user.is_active = 'R';
					User.Update(user).then(function(){
						vm.message.type = 'warning';
						vm.message.text = 'The account for '+user.firstname+' '+user.lastname+' has been rejected successfully. An email has been sent to '+user.email+'.';
					});
				}
			});

		}


		function register() {
			vm.dataLoading = true;
			User.Create(vm.user)
			.then(function (user) {
				console.log(user.permissions);
				console.log('ici2');
				Authentication.setCredentials(user.user_id,user.login, user.code, user.permissions,user.institution);
				$location.path('/validationRequired');
			});
		}
	}

})();
