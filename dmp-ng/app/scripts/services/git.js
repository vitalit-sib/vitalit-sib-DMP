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
	 * @ngdoc service
	 * @name dmpApp.News
	 * @description
	 * # News
	 * Factory in the dmpApp.
	 */
	angular.module('dmpApp')
	.factory('Git', Git);

	Git.$inject = ['Restangular'];
	function Git(Restangular,Authentication){
		var service = {};
		service.git = Restangular.all('git');
		service.git.getList().then(function(version) {
			service.version = version.plain()[0];
		});
		
		
		return service;

		
	}

})();
