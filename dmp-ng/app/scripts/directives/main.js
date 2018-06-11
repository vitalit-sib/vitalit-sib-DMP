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
	.directive('compareTo', compareTo)
	.directive('checkPassword', checkPassword)
	.directive('emailObf',emailObf);


	function  compareTo() {
		return {
			require: "ngModel",
			scope: {
				otherModelValue: "=compareTo"
			},
			link: function(scope, element, attributes, ngModel) {
				ngModel.$validators.compareTo = function(modelValue) {
					return modelValue == scope.otherModelValue;
				};

				scope.$watch("otherModelValue", function() {
					ngModel.$validate();
				});
			}
		};
	};

	function checkPassword(){
		return {
			require: 'ngModel',
			link: function(scope, element, attr, ngModel) {
				function pwValidation(value) {
					if (value.length >= 8) {
						ngModel.$setValidity('length', true);
					} else {
						ngModel.$setValidity('length', false);
					}
					if (value.match(/\d/)) {
						ngModel.$setValidity('numeric', true);
					} else {
						ngModel.$setValidity('numeric', false);
					}
					if (value.match(/[a-z]/)) {
						ngModel.$setValidity('lowercase', true);
					} else {
						ngModel.$setValidity('lowercase', false);
					}
					if (value.match(/[A-Z]/)) {
						ngModel.$setValidity('uppercase', true);
					} else {
						ngModel.$setValidity('uppercase', false);
					}
					return value;
				}
				ngModel.$parsers.push(pwValidation);
			}
		};
	};

	emailObf.$inject = [];
	function emailObf(){
	    return {
	      restrict: 'E',
	      scope: {},
	      link: function(scope, elem, attr) {
			  if (attr.icon){
				  var i = document.createElement('i');
				  i.className = "fa fa-envelope";
				  i.style.marginRight = '8px';
				  elem[0].appendChild(i);
			  }
			  var link = document.createElement('a');
			  link.href = 'mailto:'+attr.name+"@"+attr.domain;
			  if (attr.title) link.href += "?subject="+attr.title;
			  elem[0].appendChild(link);
			  if (attr.content){
				  link.innerHTML = attr.content;
			  }
			  else{
				  var name = document.createElement('span');
				  name.innerHTML = attr.name;
				  var at = document.createElement('i');
				  at.className = "fa fa-at";
				  var at2 = document.createElement('span');
				  at2.innerHTML = '@';
				  at2.className = 'sr-only';

				  var domain = document.createElement('span');
				  domain.innerHTML = attr.domain;

				  link.appendChild(name);
				  link.appendChild(at);
				  link.appendChild(at2);
				  link.appendChild(domain);
			  }
			  if (attr.nav){
				  link.className = 'navLink';
			  }
	      }
	    };
	  };

})();
