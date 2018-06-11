'use strict';

describe('Controller: ExperimentsCtrl', function () {

  // load the controller's module
  beforeEach(module('melanomxApp'));

  var ExperimentsCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    ExperimentsCtrl = $controller('ExperimentsCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});
