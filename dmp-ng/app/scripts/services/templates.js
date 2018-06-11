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
(function () {
    'use strict';

	/**
	 * @ngdoc service
	 * @name dmpApp.User
	 * @description
	 * # User
	 * Factory in the dmpApp. Create, Delete, Update, Get Users.
	 */

    angular
        .module('dmpApp')
        .factory('Templates', Templates);

    Templates.$inject = ['Restangular'];
    function Templates(Restangular) {
        var service = {};
		
		
		// var templates = Restangular.one('templates').get();
// 		templates.then(function (data) {
// 			vm.templates = data.plain() ;
//
// 			vm.templates2 = data.plain() ;
// 			angular.forEach(vm.templates2, function(titles_and_templates) {
// 				angular.forEach(titles_and_templates.templates, function(template) {
//
// 					template.text=template.text.replace(/(\[[^\]]+\])/,'<span style ="color:red">$1</span>');
//
//
// 				});
//
// 			});
//
//
// 		});
		
		
		

        service.GetTemplates = GetTemplates;
       

        return service;


        function GetTemplates(authdata) {
			return Restangular.one("templates").get().then(handleSuccess, handleError('Error getting user by templates'));
        }

        // private functions

        function handleSuccess(data) {
            return data;
        }

        function handleError(error) {
            return function () {
                return { success: false, message: error };
            };
        }
    }

})();
