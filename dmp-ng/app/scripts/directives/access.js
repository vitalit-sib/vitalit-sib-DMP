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
	* @ngdoc directive
	* @name dmpApp.directive:compareTo
	* @description used to compare to models. Used initially to compare password and confirm password
	* # compareTo
	*/
	angular.module('dmpApp')
	.directive('access',access);

	access.$digest = ['Authorization','localStorageService'];
	function access(Authorization,localStorageService){
        return {
          restrict: 'A',
			scope: {},
          link: function (scope, element, attrs) {
              var makeVisible = function () {
                      element.removeClass('hidden');
                  },
                  makeHidden = function () {
                      element.addClass('hidden');
                  },
                  determineVisibility = function (resetFirst) {
                      var result;
                      if (resetFirst) {
                          makeVisible();
                      }

                      result = Authorization.authorize(true, roles, attrs.accessPermissionType);
                      if (result) {
                          makeVisible();
                      } else {
                          makeHidden();
                      }
                  },
				  run = function(){
		              if (roles.length > 0) {
		                  determineVisibility(true);
		              }
				  },
                  roles = attrs.access.split(',');

				  run();
		  		scope.$watch(function(){return localStorageService.get('currentUser');},function(n,o){
		  			if (n != o){
		  				run();
		  			}
		  		},true);


          }
        };

	}
})();