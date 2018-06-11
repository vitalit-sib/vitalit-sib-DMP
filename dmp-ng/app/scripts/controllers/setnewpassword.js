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


(function(){
	'use strict';

	/**
	 * @ngdoc function
	 * @name dmpApp.controller:SetnewpasswordCtrl
	 * @description
	 * # SetnewpasswordCtrl
	 * Controller of the dmpApp. Used to set new password when user logs in with login and temporary password
	 */
	angular.module('dmpApp')
	.controller('SetnewpasswordCtrl', setPasswordCtrl);

	setPasswordCtrl.$inject = ['User','Authentication', '$location', 'toastr', 'siteTitle'];
	function setPasswordCtrl(User,Authentication, $location, toastr, siteTitle){
		var vm = this;
		vm.siteTitle = siteTitle.name;
		vm.setPassword = setPassword;

		function setPassword(){
			var user = User.GetByAuthdata(Authentication.currentUser.authdata).then(function(user){
				user.password = vm.user.password;
				user.password2 = vm.user.password;
				User.Update(user).then(function(user){
					toastr.success('Password set successfully');
					$location.path("/");
				})
			})
		}
	}
})();
