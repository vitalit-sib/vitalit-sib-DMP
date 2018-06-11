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


/*global angular, console*/
(function(){
	'use strict';

	/**
	* @ngdoc controller
	* @name dmpApp.controller:UserCtrl
	* @description
	* # UserCtrl
	* Controller of the dmpApp. View and Edit Users.
	*/
	angular.module('dmpApp')
	.controller('UserCtrl', UserCtrl);

	UserCtrl.$inject = ['User','Group','siteTitle', 'Authentication','$routeParams', 'toastr', '$filter', '$location','_','Restangular','$timeout'];
	function UserCtrl(User, Group, siteTitle, Authentication, $routeParams, toastr, $filter, $location, _,Restangular,$timeout){
		var vm = this;
		vm.getUser = getUser;
		vm.listUsers = listUsers;
		vm.changeEditorStatus = changeEditorStatus;
		vm.toggleGroup = toggleGroup;
		vm.updateUser = updateUser;
		vm.users = [];
		vm.siteTitle = siteTitle.name;
		var user_id = $routeParams.user_id;
		var loggedUser = Authentication.getCredentials();
		if (user_id) getUser(user_id);
		else listUsers();


		///////////////////////

		function getUser(user_id){
			// Group.getAll().then(function(){
// 				vm.groups = Group.groups;
// 			});
			User.GetById(user_id).then(function(user){
				vm.user = user;
				if (vm.user.newsletter == null) vm.user.newsletter= "N";
				// var leader = false;
// 				for(var i = 0; i < user.groups.length; i++){
// 					if (user.groups[i].leader_id == loggedUser.user_id) leader = true;
// 				}
				if (+loggedUser.user_id !== +user_id && loggedUser.permissions.indexOf('admin') === -1 && !leader){
					toastr.error('Sorry, you cannot edit this account.','Permission denied');
					$location.path("/users");
				}
				// return user;
			});

		}

		function listUsers(){
			vm.getters = {
				user: function(value){
					return value.lastname+value.firstname;
				}
			};
			vm.displayedCollection = [].concat(vm.users);
			User.GetAll().then(function(users){
				vm.users = users;
				if (vm.users !== undefined || vm.users.length != 0){
					var institution_link = vm.users[0].institution;
					vm.institution = institution_link.split(".ch")[0];
				}
			});
		}

		function changeEditorStatus(user){
			console.log(user);
			if (user.is_editor == true) user.is_editor ="Y";
			if (user.is_editor == false) user.is_editor ="N";


			User.Update(user)

		}

		function toggleGroup(group){
			if (!user_id) return;
			var groups = vm.user.permissions;
			var groupIdx = groups.indexOf('group_'+group.group_id);
			if (groupIdx === -1){
				vm.user.permissions.push('group_'+group.group_id);
				vm.user.groups.push(group);
			}
			else{
				vm.user.permissions.splice(groupIdx,1);
				var userGroupIdx = _.findIndex(vm.user.groups,function(ugroup){return ugroup.group_id == group.group_id;});
				if (userGroupIdx > -1) vm.user.groups.splice(userGroupIdx,1);
			}
		}

		function updateUser(){
			User.Update(vm.user).then(function(user){
				vm.user = user;
				toastr.success('Account updated successfully','Success');
			});
		}
	}
})();
