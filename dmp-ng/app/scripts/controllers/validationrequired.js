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
	 * @ngdoc controller
	 * @name dmpApp.controller:ValidationrequiredCtrl
	 * @description
	 * # ValidationrequiredCtrl.
	 * Controller of the dmpApp. Used when a user tries to connect with an account that is not yet valid.
	 */
	angular.module('dmpApp')
	  .controller('ValidationrequiredCtrl', validationRequiredCtrl);

	  validationRequiredCtrl.$inject = ['Authentication', 'loggedUser','$location'];
	  function validationRequiredCtrl(Authentication, loggedUser, $location){
		  var vm = this;

		  vm.user = loggedUser;
		  if (vm.user.is_active == 'Y') { $location.path("/"); }
	  }

})();
