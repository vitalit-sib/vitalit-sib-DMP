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
(function(){
	'use strict';

	/**
	* @ngdoc overview
	* @name dmpApp
	* @description
	* # dmpApp
	*
	* Main module of the application.
	*/
	angular
	.module('dmpApp', [
		'ngAnimate',
		'ngCookies',
		'ngResource',
		'ngRoute',
		'ngSanitize',
		'ngTouch',
		'libs',
		'toastr',
		'restangular',
		'config',
		'LocalStorageModule',
		'smart-table',
		'ui.tinymce',
		'angularFileUpload',
		'ui.bootstrap',
		'angular.filter',
		'angular-clipboard',
		'ui.select',
		'ui.bootstrap',
		'ui.checkbox',
		'rzModule',
		'btford.markdown'
	])

	.constant('siteTitle',{name: 'DMP Canvas Generator'})

	.config(configRoute)
	.config(exceptionConfig)
	.config(restangularProvider)
	.config(LocalStorageProvider)
	.config(locationProvider)
	.config(locationProvider)
	.config(toastrProvider)
	.run(run)
	.run(restangularInterceptor);

	/**
	* @ngdoc overview
	* @name configRoute
	* @description
	* # the routing of the application.
	* # controllerAs enables to access scope variable as vm.variable instead of $scope.variable
	* # access: evaluated on routeChangeStart.
	* ## LoginRequired (true|false)
	* ## permissions (null, one or several permissions from user.permissions. e.g. active, admin, project_1).
	* ## PermissionCheckType: (one|all). Whether one of or all of the above permissions are required.
	* # PAGEKEY: using to activate the nav tabs in the navbar. See routeChangeSuccess
	*
	* Routing of the application
	*/
	configRoute.$inject = ['$routeProvider','ENV'];
	function configRoute($routeProvider,ENV) {
		$routeProvider
		.when('/login', {
			// templateUrl: 'views/login.html',
			templateUrl: function(){
				return 'views/login.html';
			},
			controller: 'LoginCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			pageKey: 'HOME'

		})
		.when('/loginaai', {
			// templateUrl: 'views/login.html',
			templateUrl: function(){
				return 'views/login_switch.html';
			},
			controller: 'LoginCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			pageKey: 'HOME'

		})
		.when('/logout', {
			template: '',
			controller: 'LoginCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			pageKey: 'HOME'

		})
		.when('/login/user_id/:user_id/login/:login/code/:code/permissions/:permissions/institution/:institution', {
			templateUrl: 'views/login.html',
			controller: 'LoginCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			pageKey: 'HOME'

		})
		.when('/permissionDenied', {
			templateUrl: 'views/permissiondenied.html',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			pageKey: 'HOME'
		})
		.when('/register', {
			templateUrl: 'views/register.html',
			controller: 'RegisterCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},

			pageKey: 'HOME'
		})
		.when('/activation/:param', {
			templateUrl: 'views/activation.html',
			controller: 'RegisterCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			resolve:{
				Groups: function(Restangular){
					return Restangular.all('group').getList();
				}
			},
			pageKey: 'HOME'
		})
		.when('/reject/:param', {
			templateUrl: 'views/activation.html',
			controller: 'RegisterCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			resolve:{
				Groups: function(Restangular){
					return Restangular.all('group').getList();
				}
			},
			pageKey: 'HOME'
		})
		.when('/validationRequired', {
			templateUrl: 'views/validationrequired.html',
			controller: 'ValidationrequiredCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: [],
				permissionCheckType: 'one'
			},
			resolve: {
				loggedUser: function(User,Authentication){
					return User.GetByAuthdata(Authentication.currentUser.authdata);
				}
			},
			pageKey: 'HOME'
		})
		.when('/forgetPassword', {
			templateUrl: 'views/forgetpassword.html',
			controller: 'ForgetpasswordCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			pageKey: 'HOME'
		})
		.when('/setnewpassword', {
			templateUrl: 'views/setnewpassword.html',
			controller: 'SetnewpasswordCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['active'],
				permissionCheckType: 'one'
			},
			pageKey: 'HOME'

		})

		.when('/request/:action/:code',{
			templateUrl: 'views/activation.html',
			controller: 'RequestCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: false,
				permissions: [],
				permissionCheckType: 'one'
			},
			resolve:{
				requestData: function(Restangular,$route){
					var action = $route.current.params.action;
					var code = $route.current.params.code;
					return Restangular.one('request',action).one('code',code).get();
				}
			},
			pageKey: 'EXPERIMENTS'
		})
		.when('/admin', {
			templateUrl: 'views/admin.html',
			controller: 'AdminCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['active','admin'],
				permissionCheckType: 'all'
			},
			pageKey: 'ADMIN'
		})
		.when('/users', {
			templateUrl: 'views/userList.html',
			controller: 'UserCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['admin','editor'],
				permissionCheckType: 'one'
			},
			pageKey: 'USERS'
		})
		.when('/user/:user_id', {
			templateUrl: 'views/user.html',
			controller: 'UserCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['active'],
				permissionCheckType: 'one'
			},
			pageKey: 'USERS'
		})
		.when('/groups', {
			templateUrl: 'views/groups.html',
			controller: 'GroupsCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['admin'],
				permissionCheckType: 'one'
			},
			resolve:{
				groupList: function(Restangular){
					return Restangular.all('group').getList({'members':'yes'});
				}
			},
			pageKey: 'USERS'
		})
		.when('/form', {
			templateUrl: 'views/form.html',
			controller: 'FormCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['active','admin'],
				permissionCheckType: 'one'
			},
			pageKey: 'CANVAS'
		})
		.when('/acknowledgements', {
			templateUrl: 'views/acknowledgements.html',
			controller: 'FormCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['active','admin'],
				permissionCheckType: 'one'
			},
			pageKey: 'ACKNOWLEDGEMENTS'
		})
		.when('/news', {
					templateUrl: 'views/news.html',
					controller: 'NewsCtrl',
					controllerAs: 'vm',
					access:{
						loginRequired: true,
						permissions: ['active'],
						permissionCheckType: 'one'
					},
					pageKey: 'NEWS'

		})
		.when('/news/:newsId', {
					templateUrl: 'views/news/edit.html',
					controller: 'NewsEditCtrl',
					controllerAs: 'vm',

					access:{
						loginRequired: true,
						permissions: ['admin','editor'],
						permissionCheckType: 'one'
					},
					pageKey: 'NEWS'

		})
		.when('/templates', {
			templateUrl: 'views/templates.html',
			controller: 'TemplatesCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['admin','editor'],
				permissionCheckType: 'one'
			},
			pageKey: 'TEMPLATES'
		})
		.when('/preview', {
			templateUrl: 'views/templatespreview.html',
			controller: 'TemplatesPreviewCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['admin','editor'],
				permissionCheckType: 'one'
			},
			pageKey: 'PREVIEW'
		})
		.when('/documentation', {
			templateUrl: 'views/dbDocumentation.html',
			controller: 'DocumentationCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['admin','editor'],
				permissionCheckType: 'one'
			}
		})
		.when('/test', {
			templateUrl: 'views/test.html',
			controller: 'TestCtrl',
			controllerAs: 'vm',
			access:{
				loginRequired: true,
				permissions: ['active'],
				permissionCheckType: 'one'
			}
		})
		.when('/help', {
			templateUrl: 'views/How_to_dmp_editor.html',
			access:{
				loginRequired: true,
				permissions: ['admin','editor'],
				permissionCheckType: 'one'
			}
		})

		.otherwise({
			redirectTo: '/form'
		});
	}



	/**
	* @ngdoc overview
	* @name exceptionConfig
	* @description
	* # handling of the javascript Exceptions, usually thrown by angular
	* # actually, only sends to console.
	*
	* Exception handler
	*/

	exceptionConfig.$inject = ['$provide'];

	function exceptionConfig($provide) {
		$provide.decorator('$exceptionHandler', extendExceptionHandler);
	}
	extendExceptionHandler.$inject = ['$delegate','$injector','$log'];
	function extendExceptionHandler($delegate,$injector,$log) {
		return function(exception, cause) {
			$delegate(exception, cause);
			var errorData = {
				exception: exception,
				cause: cause
			};
			/**
			* Could add the error to a service's collection,
			* add errors to $rootScope, log errors to remote web server,
			* or log locally. Or throw hard. It is entirely up to you.
			* throw exception;
			*/
			$log.error(exception.message);
		};
	}

	/**
	* @ngdoc overview
	* @name LocalStorageProvider
	* @description
	* # setPrefix of localStorage variable
	* # setStorageType (session | local)
	* # setNotify (??)
	* LocalStorage
	*/
	LocalStorageProvider.$inject = ['localStorageServiceProvider'];
	function LocalStorageProvider(localStorageServiceProvider){
		localStorageServiceProvider
		.setPrefix('dmpApp')
		.setStorageType('sessionStorage')
		.setNotify(true, true)

	}

	/**
	* @ngdoc overview
	* @name RestangularProvider
	* @description
	* # setBaseUrl: URL of the backend. index.php of SLIM in this project
	* # setDefaultHttpFields({cache: true}): whether to cache the http queries
	* Restangular
	*/

	restangularProvider.$inject = ['RestangularProvider','ENV'];
	function restangularProvider(RestangularProvider,ENV){
		RestangularProvider.setBaseUrl(ENV.serverURL);
		// RestangularProvider.setDefaultHttpFields({cache: true});
	}


	locationProvider.$inject = ['$locationProvider'];
	function locationProvider($locationProvider){
		$locationProvider.hashPrefix('');
	}

	toastrProvider.$inject = ['toastrConfig'];
	function toastrProvider(toastrConfig){
	  angular.extend(toastrConfig, {
	    autoDismiss: false,
			allowHtml: true,
	    containerId: 'toast-container',
	    maxOpened: 5,
			closeButton: true,
	    closeHtml: '<button>&times;</button>',
	    newestOnTop: true,
	    positionClass: 'toast-top-right',
	    preventDuplicates: false,
	    preventOpenDuplicates: true,
	    target: 'body'
	  });
	}


	/**
	* @ngdoc overview
	* @name run
	* @description
	* # gets currentUser from localStorage. If, currentUser, set default Authorization header for all http requests
	* # routeChangeStart: check access permissions using the Authorization service
	* # routeChangeSuccess: set / unset active class to nav elements (in navbar) based on PAGEKEY parameter.
	*
	* Exception handler
	*/


	run.$inject = ['$rootScope', '$location', 'localStorageService', '$http','Authentication','Authorization'];
	function run($rootScope, $location, localStorageService, $http,Authentication,Authorization) {
		// keep user logged in after page refresh
		Authentication.currentUser = localStorageService.get('currentUser') || {};
		if (Authentication.currentUser.username !== undefined) {
			$http.defaults.headers.common['Authorization'] = 'Basic ' + Authentication.currentUser.authdata; // jshint ignore:line
		}

		$rootScope.$on('$routeChangeStart', function (event,next) {
			var authorised;
			if (next.access !== undefined) {
				authorised = Authorization.authorize(next.access.loginRequired,
					next.access.permissions,
					next.access.permissionCheckType);
					if (!authorised){
						if (Authentication.currentUser.username){
							$location.path('/permissionDenied').replace(); // replace is to avoid storing in browser history stack
						}
						else $location.path('/login');
					}
				}
			});

			$rootScope.$on("$routeChangeSuccess",
			function (angularEvent,	currentRoute) {
				var pageKey = currentRoute.pageKey;
				$(".pagekey").toggleClass("active", false);
				$(".pagekey_" + pageKey).toggleClass("active", true);
			});

		}

		// get error from Restangular (PHP) //

		restangularInterceptor.$inject = ['$window','Restangular','toastr','$log'];
		function restangularInterceptor($window,Restangular,toastr,$log){
			Restangular.setErrorInterceptor(
				function(response) {
					if (response.status == 401) {
						toastr.info("Login required... ");
						$window.location.href='/login';
					} else if (response.status == 404) {
						toastr.info("Resource not available...");
					} else if (response.status == 500) {
						$log.error(response.data);
					} else if (response.status == 501) {
						toastr.error(response.data,'ERROR');
					} else {
						toastr.warning("Response received with HTTP error code: " + response.status );
					}
					return false; // stop the promise chain
				}
			);
		}



	})();
