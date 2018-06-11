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
	 * @ngdoc controller
	 * @name vikmApp.controller:NewsCtrl
	 * @description
	 * # NewsCtrl
	 * Controller of the vikmApp
	 * Gets the news from the backend.
	 * Redirect to news/new to create a new news.
	 */
	angular.module('dmpApp')
	.controller('NewsCtrl', NewsCtrl);

	NewsCtrl.$inject = ['$location', 'Restangular', 'Authentication', 'siteTitle'];
	function NewsCtrl($location, Restangular,Authentication, siteTitle){
		var vm = this;
		vm.newsIsActive = 'Y';
		vm.siteTitle = siteTitle.name;
		vm.initNews = initNews;

	/**
	 * @ngdoc function
	 * @name vikmApp.controller:NewsCtrl:getNews
	 * @description
	 * # getNews
	 * Gets the news from the backend relevant to the current project.
	 */
		(function getNews(){
		
			Restangular.all('news').getList().then(function(data){
				vm.news = data;
			});
		})();

		//////////////

		function initNews(){
			$location.path('/news/new');
		}



	}
})();
