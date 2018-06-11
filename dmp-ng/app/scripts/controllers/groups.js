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
	 * @name dmpApp.controller:GroupsCtrl
	 * @description
	 * # manage user groups
	 * Controller of the dmpApp
	 */
	angular.module('dmpApp')
	.controller('GroupsCtrl', GroupsCtrl);

	GroupsCtrl.$inject = ['siteTitle','groupList','Restangular','toastr','User','Authentication','_'];
	function GroupsCtrl(siteTitle,groupList,Restangular,toastr,User,Authentication,_){
		var vm = this;
		vm.groups = groupList;
		vm.displayedCollection = [].concat(vm.groups);
		vm.editGroup = editGroup;
		vm.deleteGroup = deleteGroup;
		vm.removeMember = removeMember;
		vm.createGroup = createGroup;
		vm.toggleProject = toggleProject;
		vm.showNewGroup = false;

		///////

		/**
		 * @ngdoc function
		 * @name editGroup
		 * @param group_id: int. ID of the group to edit.
		 * @description
		 * # get the group values in scope
		 * # get all users. This is useful to propose group leaders.
		*/
		function editGroup(group_id){
			var groupIdx = _.findIndex(vm.groups,function(group){return group.group_id == group_id; });
			vm.newGroup = vm.groups[groupIdx];
			vm.showNewGroup = true;
			User.GetAll().then(function(data){
				vm.users = data;
			});
		}

		/**
		 * @ngdoc function
		 * @name deleteGroup
		 * @param group_id: int. ID of the group to remove.
		 * @description
		 * # removes a group. Sends to backend
		*/
		function deleteGroup(group_id){
			Restangular.one('group',group_id).remove().then(function(){
				toastr.success('Group removed successfuly','Success');
				var idx = _.findIndex(vm.groups,function(group){return group.group_id == group_id;});
				if (idx > -1) vm.groups.splice(idx,1);
			});
		}

		/**
		 * @ngdoc function
		 * @name removeMember
		 * @param group_id: int. ID of the group.
		 * @param user_id: int. ID of the user.
		 * @description
		 * # removes a member from a group
		*/

		function removeMember(group_id,user_id){
			var groupIdx = _.findIndex(vm.groups,function(group){return group.group_id == group_id;});
			User.GetById(user_id).then(function(user){
				var idx = _.findIndex(user.groups,function(group){return group.group_id == group_id;});
				if (idx > -1) user.groups.splice(idx,1);
				User.Update(user).then(function(){
					var idx = _.findIndex(vm.groups[groupIdx].members,function(member){return member.user_id == user_id;});
					if (idx > -1) vm.groups[groupIdx].members.splice(idx,1);
				});
			});
		}


		/**
		 * @ngdoc function
		 * @name createGroup
		 * @description
		 * # if showNewGroup is false, initialise new group object
		 * # if newGroup.group_id => put to update the group
		 * # else post to create the group.
		*/

		function createGroup(){
			if (!vm.showNewGroup){
				vm.showNewGroup = true;
				vm.newGroup = {
					group_id: null,
					name: '',
					institution: '',
					leader_id: '',
					members: [{user_id: Authentication.currentUser.user_id}],
					projects: [{project_id: Authentication.currentUser.project_id}]
				};
				User.GetAll().then(function(data){
					vm.users = data;
				});
			}
			else{
				if (vm.newGroup.group_id){
					vm.newGroup.put().then(function(){
						vm.showNewGroup = false;
						toastr.success('Group modified successfully','Success');
					});
				}
				else{
					vm.groups.post(vm.newGroup).then(function(group_id){
						Restangular.one('group',group_id).get({'members':'yes'}).then(function(group){
							if (group) vm.groups.push(group);
						});
						// vm.groups = data;
						vm.showNewGroup = false;
						toastr.success('Group created successfully','Success');
					});
				}
			}
		}

		/**
		 * @ngdoc function
		 * @name toggleProject
		 * @param group
		 * @param project
		 * @description
		 * # assign/remove project permission to a group.
		*/

		function toggleProject(group,project){
			var add = (vm["project"+project.project_id+"group"+group.group_id]);
			if (add) group.projects.push(project);
			else{
				var projectIdx = _.findIndex(group.projects,function(p){return p.project_id == project.project_id;});
				if (projectIdx > -1) group.projects.splice(projectIdx,1);
				else console.error("project not found");
			}
			group.put().then(function(){
				toastr.success('Project '+(add?"added":"removed")+" successfuly",'success');
			});
		}
	}
})();
