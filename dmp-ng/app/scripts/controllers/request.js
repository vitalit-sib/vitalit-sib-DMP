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
	* @name dmpApp.controller:RequestCtrl
	* @description
	* # RequestCtrl
	* Controller of the dmpApp to display result of the account activation / rejection.
	*/
	angular.module('dmpApp')
	.controller('RequestCtrl', RequestCtrl);

	RequestCtrl.$inject = ['requestData'];
	function RequestCtrl(requestData){
		var vm = this;
		vm.message = requestData;
	}
})();

