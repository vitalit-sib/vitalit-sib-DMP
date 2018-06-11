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
(function () {
  'use strict';

  /**
   * @ngdoc controller
   * @name dmpApp.controller:GroupsCtrl
   * @description
   * # manage user groups
   * Controller of the dmpApp
   */
  angular.module('dmpApp')
    .controller('DocumentationCtrl', DocumentationCtrl);

  DocumentationCtrl.$inject = ['$scope','Restangular'];
  function DocumentationCtrl($scope, Restangular) {
    var vm = this;
	var ParamKeyNames = Restangular.all('paramKeyNames');
	
	ParamKeyNames.getList().then(function (data) {
		vm.key_names = data.join(', ');
		
	});

  }
})();
