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
(function () {
  'use strict';

  /**
   * @ngdoc controller
   * @name dmpApp.controller:UserCtrl
   * @description
   * # UserCtrl
   * Controller of the dmpApp. View and Edit Users.
   */
  angular.module('dmpApp')
    .controller('TemplatesCtrl', TemplatesCtrl)
    .controller('helpModalController', helpModalController);

  TemplatesCtrl.$inject = ['User', 'Group', 'siteTitle', 'Authentication', '$routeParams', 'toastr', '$filter', '$location', '_', 'Restangular', '$sce', 'Templates', '$uibModal', '$scope'];
  function TemplatesCtrl(User, Group, siteTitle, Authentication, $routeParams, toastr, $filter, $location, _, Restangular, $sce, Templates, $uibModal, $scope) {
    var vm = this;

    if (Authentication.currentUser.institution)vm.institution = Authentication.currentUser.institution.split(".")[0];
    vm.select_section = select_section;
    vm.select_parameter = select_parameter;
    vm.saveTemplates = saveTemplates;
    vm.select_parameter_type = select_parameter_type;
    vm.changeRecommendation = changeRecommendation;
    vm.saveRecommendation = saveRecommendation;
    vm.previewFormatting = previewFormatting;
    vm.openHelp = openHelp;
    vm.trustAsHtml = trustAsHtml;
    vm.isResetChanges = isResetChanges;
    vm.resetChanges = resetChanges;
    vm.toggleTemplate = toggleTemplate;
	vm.saveAboutContact = saveAboutContact;
	
    vm.section_button = "Choose a section to modify";
    vm.parameter_type_button = "Choose a recommendation category";
    vm.editText = false;
    vm.showPreview = false;
    vm.negation = "";
    vm.alertOK = false;
    var specificTemplate = Restangular.all('/templateSpecific');
    var recommendation = Restangular.all('recommendation');
	var aboutContact = Restangular.all('aboutContact');

    // This will query /accounts and return a promise.
	aboutContact.getList().then(function (data) {
		vm.aboutContactData = data;
		vm.aboutContact = data.plain()[0];
		
	})

    recommendation.getList().then(function (data) {
      vm.recommendationData = data;
      vm.recommendation = data.plain()[0];
      angular.forEach(vm.recommendation.parameter_types, function (parameter_types) {
        angular.forEach(parameter_types, function (params) {
          if (params.recommendation_id == null) {
            params.recommendation = 'displayed'
          }
        })
      });
      angular.forEach(vm.recommendation.recommendation, function (recommendation) {
        if (recommendation.recommendation == 'not displayed') {
          recommendation.recommendation = "displayed";
          vm.recommendation_id = recommendation.recommendation_id;
        }
      })

    });

    Templates.GetTemplates().then(function (templates) {
      vm.templates = templates.plain();
      // vm.templates = dataJSON;
      vm.footer = {};
      angular.forEach(vm.templates, function (item, idx) {
        item.selected = Object.keys(vm.templates[idx].templates)[0];
      });
      previewFormatting();
    });

    function openHelp() {
      var modalInstance = $uibModal.open({
        animation: true,
        ariaLabelledBy: 'modal-title',
        ariaDescribedBy: 'modal-body',
        templateUrl: 'views/modal/How_to_dmp_editor.html',
        size: "lg",
        controller: function () {
          var $ctrl = this;
          $ctrl.close = function () {
            modalInstance.close();
          }
        },
        controllerAs: '$ctrl'
      });

      modalInstance.result.then(function () {
      }, function () {
      });
    }

    function select_section(section, title) {
      vm.section = section;
      vm.showTextBox = true;
      vm.section_button = (title != null) ? section + ' ' + title : section;

    }

    function select_parameter(parameter, param) {
      vm.templates[param.section_number].selected = parameter;
      vm.parameter_button = parameter;
      vm.showTextBox = true;
    }

    function saveTemplates() {
      specificTemplate.post(vm.templates).then(function (data) {
        if (data === true) toastr.success('Your template has been updated');
      });
    }

    function select_parameter_type(type) {
      vm.type = type;
      vm.parameter_type_button = type;
    }

    function changeRecommendation(params, id) {
      params.recommendation_id = id;
    }

    function saveRecommendation() {
      vm.recommendationData[0].recommendation = vm.recommendation.recommendation;
      vm.recommendationData[0].parameter_types = vm.recommendation.parameter_types;
      vm.recommendationData.post();
    }
	
    function saveAboutContact(){
       vm.aboutContactData[0].about = vm.aboutContact.about;
        vm.aboutContactData[0].contact = vm.aboutContact.contact;
        vm.aboutContactData.post();
  	
    }

    function isResetChanges(section, type) {

      var user, dmp, institution, changeToTemplate;
      if (type == 'heading') {
        user = section.heading_user;
        dmp = section.heading;
        institution = section.heading_institution;
        if (!section.changeToHeader) {
          section.changeToHeader = (institution == null) ? vm.institution : 'DMP';
        }
        changeToTemplate = section.changeToHeader
      } else if (type == 'text') {
        user = section.text_user;
        dmp = section.text;
        institution = section.text_institution;
        if (!section.changeToText) {
          section.changeToText = (institution == null) ? vm.institution : 'DMP';
        }
        changeToTemplate = section.changeToText;
      } else if (type == 'footer') {
        user = section.footer_user;
        dmp = section.footer;
        institution = section.footer_institution;
        if (!section.changeToFooter) {
          section.changeToFooter = (institution == null) ? vm.institution : 'DMP';
        }
        changeToTemplate = section.changeToFooter
      }
      section.isReset = false;

      if (user != dmp && institution == null) section.isReset = true;
      if (user != institution && dmp == null) section.isReset = true;
      if (institution != null && dmp != null) {
        if (changeToTemplate == 'DMP' && user != institution) section.isReset = false;
        if (changeToTemplate == vm.institution && user != dmp) section.isReset = true;
      }
      return section.isReset;
    }

    function toggleTemplate(section, type) {
      if (type == 'heading') {
        section.heading_user = (section.changeToHeader == vm.institution) ? section.heading_institution : section.heading;
        section.changeToHeader = (section.changeToHeader == vm.institution) ? 'DMP' : vm.institution;
      } else if (type == 'text') {
        section.text_user = (section.changeToText == vm.institution) ? section.text_institution : section.text;
        section.changeToText = (section.changeToText == vm.institution) ? 'DMP' : vm.institution;
      } else if (type == 'footer') {
        section.footer_user = (section.changeToFooter == vm.institution) ? section.footer_institution : section.footer;
        section.changeToFooter = (section.changeToFooter == vm.institution) ? 'DMP' : vm.institution;
      }
    }

    function resetChanges(section, type) {
      if (type == 'heading') {
        section.heading_user = (section.changeToHeader == vm.institution) ? section.heading : section.heading_institution;
      } else if (type == 'text') {
        section.text_user = (section.changeToText == vm.institution) ? section.text : section.text_institution;
      } else if (type == 'footer') {
        section.footer_user = (section.changeToFooter == vm.institution) ? section.footer : section.footer_institution;
      }
    }

    function previewFormatting() {
      vm.templates_preview = angular.copy(vm.templates);
      angular.forEach(vm.templates_preview, function (titles_and_templates) {
        angular.forEach(titles_and_templates.templates, function (template) {
          template.text = template.text.replace(/(\[[^\]]+\])/, '<span style ="color:red">$1</span>');
        });
        if (titles_and_templates.footer) titles_and_templates.footer = titles_and_templates.footer.replace(/(\[[^\]]+\])/, '<span style ="color:red">$1</span>');
        if (titles_and_templates.heading)titles_and_templates.heading = titles_and_templates.heading.replace(/(\[[^\]]+\])/, '<span style ="color:red">$1</span>');
      });
    }

    function trustAsHtml(string) {
      return $sce.trustAsHtml(string);
    }
  }

  helpModalController.$inject = ['$uibModalInstance'];
  function helpModalController($uibModalInstance) {
    var $ctrl = this;
    $ctrl.salut = 'poilu';
    $ctrl.close = function () {
      $uibModalInstance.dismiss('close');
    };
  }
  

})();
