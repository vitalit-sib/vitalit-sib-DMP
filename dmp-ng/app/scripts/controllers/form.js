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
    .controller('FormCtrl', FormCtrl);

  FormCtrl.$inject = ['$scope', '$route', '$timeout', 'siteTitle', 'Restangular', 'toastr', 'User', 'Authentication', '_', '$location', 'ENV'];
  function FormCtrl($scope, $route, $timeout, siteTitle, Restangular, toastr, User, Authentication, _, $location, ENV) {
    var vm = this;
    vm.data = null;
    vm.template = null;
    vm.iso = null;
    vm.cc = null;


    vm.is_recommended = "<span class='label label-warning'><i>recommended</i></span>"
// initialize volume slider
    vm.slider = {
      minValue: 100,
      maxValue: 1000,
      options: {
        floor: 1,
        ceil: 100000,
        logScale: true,
        disabled: false,
        translate: function (value) {
          var ret;
          if (value < 1000) {
            (value > 100) ? ret = Math.round(value / 10) * 10 + " GB" : ret = value + " GB";
            return ret;
          }
          else {
            return Math.round(value / 1000) + ' TB';
          }
        }
      }
    };

// necessary for slider initiation on accordion selection change
    vm.refreshSlider = function () {
      $timeout(function () {
        $scope.$broadcast('rzSliderForceRender');
      });
    };


// object containing all user input data

    var data = Restangular.one('form').get();
    data.then(function (data) {
      vm.data = data;
      vm.data.name = '';
      vm.data.description = '';

      vm.data.datatypes.other.array = [{index: 0, value: ''}];
      vm.data.analysis.other.array = [{index: 0, value: ''}];
      vm.data.internal_existing_data.other.array = [{index: 0, value: ''}];
      vm.data.external_existing_data.other.array = [{index: 0, value: ''}];

      angular.forEach(vm.data.security_standards.params, function (iso) {
        if (iso.is_selected == true) {
          vm.iso = iso
        }
      });
      angular.forEach(vm.data.licenses.params, function (cc) {
        if (cc.is_selected == true) {
          vm.cc = cc
        }
      });
    });

    vm.changeActive = function (datatype, option, type) {
      if (type === 'meta') {
        angular.forEach(vm.data.datatypes.params, function (dt) {
          if (dt.name === datatype) {
            angular.forEach(dt.metadata, function (meta) {
              option.name === meta.name ? meta.is_selected = true : meta.is_selected = false;
            })
          }
        })
      } else if (type === 'storage') {
        if (option == 'other') {
          angular.forEach(vm.data.storage.params, function (st) {
            st.is_selected = false;
          });
          vm.data.storage.other.is_selected = true;
        } else {
          angular.forEach(vm.data.storage.params, function (st) {
            option.name === st.name ? st.is_selected = true : st.is_selected = false;
          });
          vm.data.storage.other.is_selected = false;
        }
      } else if (type === 'repo') {
        angular.forEach(vm.data.datatypes.params, function (dt) {
          if (dt.name === datatype) {
            angular.forEach(dt.repositories, function (rep) {
              option.name === rep.name ? rep.is_selected = true : rep.is_selected = false;
            })
          }
        })
      } else if (type === 'iso') {
        angular.forEach(vm.data.security_standards.params, function (iso) {
          option.name === iso.name ? iso.is_selected = true : iso.is_selected = false;
        })
      } else if (type === 'cc') {
        angular.forEach(vm.data.licenses.params, function (cc) {
          option.name === cc.name ? cc.is_selected = true : cc.is_selected = false;
        })
      } else if (type === 'anon') {
        angular.forEach(vm.data.anonymizations.params, function (anon) {
          option.name === anon.name ? anon.is_selected = true : anon.is_selected = false;
        })
      } else if (type === 'humEthic') {
        angular.forEach(vm.data.committee_human.params, function (humEthic) {
          option.name === humEthic.name ? humEthic.is_selected = true : humEthic.is_selected = false;
        })
      } else if (type === 'vertEthic') {
        angular.forEach(vm.data.committee_vertebrate.params, function (vertEthic) {
          option.name === vertEthic.name ? vertEthic.is_selected = true : vertEthic.is_selected = false;
        })
      }
    };

    vm.sliderValToString = function (val) {
      var ret;
      if (val < 1000) {
        (val > 100) ? ret = Math.round(val / 10) * 10 + " GB" : ret = val + " GB"
      } else {
        ret = Math.round(val / 1000) + " TB"
      }
      return ret;
    };

    vm.addOtherField = function (type) {
      var length = type.array.length;
      type.array.push({index: length, value: ''})
    };

    vm.rmOtherField = function (type, instance) {
      type.array.splice(instance.index, 1)
      angular.forEach(type.array, function (el, idx) {
        el.index = idx
      })
    };

    vm.formatOtherVal = function (type) {
      var otherVal = '';
      angular.forEach(type.array, function (entry, idx) {
        if (idx == 0 && entry.value.length) otherVal = entry.value;
        if (idx > 0 && entry.value.length) otherVal = otherVal + "," + entry.value
      });
      type.value = otherVal
    };

    vm.downloadform = function () {
      angular.forEach(vm.data.data.params, function (param) {
        if (param.parameter_id == '125') {
          param.value = vm.sliderValToString(vm.slider.minValue);
        } else if (param.parameter_id == '126') {
          param.value = vm.sliderValToString(vm.slider.maxValue);
        } else if (param.parameter_id == '124') {
          param.is_selected = vm.slider.options.disabled;
        }
      });

      vm.formatOtherVal(vm.data.datatypes.other);
      vm.formatOtherVal(vm.data.analysis.other);
      vm.formatOtherVal(vm.data.internal_existing_data.other);
      vm.formatOtherVal(vm.data.external_existing_data.other);
      vm.data.put().then(function (data) {
        self.location.href = ENV.serverURL + "/download/" + Authentication.currentUser.user_id + "/code/" + data;
      });
    };

    //function to load previous template
    vm.loadOldTemplate = function () {
      if (vm.template) {
        Restangular.one('template', vm.template.dmp_id).get().then(function (template) {
          var templateJson = angular.fromJson(template.dmp_info);
          angular.forEach(templateJson, function (templateValue, templateKey) {
            angular.forEach(vm.data, function (dataValue, dataKey) {
              if (dataKey == templateKey && templateKey != 'previousTemplate') {
                vm.data[dataKey] = templateValue;
              }
            });
          });
          vm.data.name = vm.template.dmp_name;
        })
      }
    };

    vm.removeTemplate = function () {

      Restangular.one('template', vm.template.dmp_id).remove().then(function () {
        angular.forEach(vm.data.previousTemplate, function (template, position) {
          if (template['dmp_id'] == vm.template.dmp_id) {
            vm.data.previousTemplate.splice(position, 1);
            vm.template = false;
            vm.data.name = "";
            vm.data.description = '';
          }
        })
      });
    };

  }
})();
