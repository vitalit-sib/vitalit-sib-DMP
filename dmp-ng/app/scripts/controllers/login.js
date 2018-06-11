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
(function () {
    'use strict';
	/**
	 * @ngdoc controller
	 * @name dmpApp.controller:LoginCtrl
	 * @description
	 * # initController to reset localStorage
	 * # gets User. Location to setnewpassword if 'is_password_reset' = Y
	 * Controller of the dmpApp
	 */

    angular
        .module('dmpApp')
        .controller('LoginCtrl', LoginController);

    LoginController.$inject = ['$location', 'Authentication', 'toastr','siteTitle','$http','$routeParams','ENV','Git','$timeout'];
    function LoginController($location, Authentication, toastr, siteTitle, $http,$routeParams,ENV,Git,$timeout) {
        var vm = this;
        vm.login = login;
		vm.siteTitle = siteTitle.name;
		vm.local = ENV.CORS;
		vm.isVitalIT = $location.host().indexOf('vital-it') > -1 || $location.host().indexOf('localhost') > -1;
		vm.openORCID = orcid;

        $timeout(function () {
         if (Git.version == "github")vm.gitHub = true;
        });


		var loggedLogin;
        (function initController() {
            // reset login status
			loggedLogin = Authentication.currentUser.username;
            Authentication.clearCredentials();
        })();


		if ($location.path().indexOf('logout') > -1){
			if (loggedLogin.match(/\d{4}-\d{4}-\d{4}-\d{4}/)){
				$http( {
				         method: 'GET',
				         url: 'https://orcid.org/userStatus.json?logUserOut=true',
				         headers: {
				           'Authorization': undefined
				         }
				       }
				     ).then(function(){
						 $location.path("/login");
				});
			}
			else $location.path("/login");
		}

		if ($routeParams.user_id){
			Authentication.setCredentials($routeParams.user_id, $routeParams.login, $routeParams.code, $routeParams.permissions.split(";"),$routeParams.institution);
			$location.path('/');
		}

		//////
	/**
	 * @ngdoc function
	 * @name dmpApp.controller:LoginCtrl:login
	 * @description
	 * # sends username and password to backend for authentication.
	 * # gets User. Location to setnewpassword if 'is_password_reset' = Y
	 * Controller of the dmpApp
	 */

		function login() {
			Authentication.login(vm.username, vm.password).then(
				function (user) {
					Authentication.setCredentials(user.user_id, vm.username, user.code, user.permissions,user.institution);
					if (user.is_password_reset == 'Y'){
						$location.path('/setnewpassword').replace();
					}
					else if (user.is_active == 'R'){
						toastr.error("This account has been rejected.","Permission denied");
					}
					else{
						$location.path('/');
					}
				}
			);
		}


		function orcid(){
			// window.location.href=ENV.serverURL.replace(/\/index.php/,"")+"/orcid/";
			window.location.href="https://dmp.vital-it.ch/orcid/";
		}
	}


})();
