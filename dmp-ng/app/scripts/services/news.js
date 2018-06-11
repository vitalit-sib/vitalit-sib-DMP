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
	.factory('News', NewsService);

	NewsService.$inject = ['Restangular','Authentication'];
	function NewsService(Restangular,Authentication){
		var service = {};
		service.news = Restangular.one('project',Authentication.currentUser.project_id).all('news');
		service.getOne = getOne;
		return service;

		function getOne(id){
			return Restangular.one('news',id).get();
		}
	}

})();
