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
	 * @name dmpApp.controller:ForgetpasswordCtrl
	 * @description
	 * # Get an email from the view. Sends email to backend. Backend set 'is_password_reset' = Y,
	 *  sends an email with the activation_code as password.
	 * and redirect to login.
	 * Controller of the dmpApp
	 */
	angular.module('dmpApp')
	.controller('ForgetpasswordCtrl', forgetPasswordCtrl);


	forgetPasswordCtrl.$inject = ['User', 'toastr','$location','siteTitle'];
	function forgetPasswordCtrl(User, toastr,$location,siteTitle){
		var vm = this;
		vm.siteTitle = siteTitle.name;
		vm.resetPassword = resetPassword;

		////////////

		function resetPassword(){
			if (!vm.email) toastr.error('Email is required',"ERROR");
			else{
				User.resetPassword(vm.email).then(function(data){
					toastr.success('An email has been sent to '+vm.email+' with a temporary password.','Password reset successfully.');
					$location.path('/login');
				})
			}

		}
	}
})();
