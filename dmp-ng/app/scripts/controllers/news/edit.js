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
	* @name dmpApp.controller:NewsEditCtrl
	* @description
	* # NewsEditCtrl
	* Controller of the dmpApp
	*/
	angular.module('dmpApp')
	.controller('NewsEditCtrl', NewsEditCtrl);

	NewsEditCtrl.$inject = ['$routeParams','News','toastr','$location','Authentication','Restangular'];
	function NewsEditCtrl($routeParams,News, toastr, $location, Authentication,Restangular){
		var vm = this;
		vm.currentProjectId = Authentication.currentUser.project_id;
		vm.submitNews = submitNews;
		vm.deleteNews = deleteNews;
		vm.minDate = new Date();
	    vm.status = {
	       opened: false
	     };
		vm.open = function($event) {
			vm.status.opened = true;
		};
		var newsId = $routeParams.newsId;
		if (newsId == 'new'){
			vm.info = {
				news_id: -1,
				title: '',
				content: '',
				is_active: 'Y',
				restricted: 'N'
			};
		}
		else{
			News.getOne(newsId).then(function(info){
				vm.info = info;
				vm.info.restricted = (info.project_id) ? 'Y' : 'N';
			});
		}



		///////
		function submitNews(){
			vm.info.project_id = (vm.info.restricted == 'Y') ? Authentication.currentUser.project_id : null;
			delete vm.info['restricted'];
			if (vm.info.news_id == -1){
        //delete the news_id value of -1 as it breaks sqlite3 db
        delete vm.info['news_id'];
        News.news.post(vm.info).then(function(){
          toastr.success('News created successfuly','success');
          $location.path("/news");
        });
      }
			else vm.info.save().then(function(){
				toastr.success('News updated successfuly','success');
				$location.path("/news");

			});
		}

		function deleteNews(){
			if (!vm.info.confirmDelete) vm.info.confirmDelete = true;
			else{
				Restangular.one('news',vm.info.news_id).remove().then(function(){
				// vm.info.remove().then(function(){
					toastr.success('News deleted successfuly','success');
					$location.path("/news");
				});
			}
		}

	}
})();
