'use strict';

describe('Controller: ForgetpasswordCtrl', function () {

  // load the controller's module
  beforeEach(module('melanomxApp'));

  var ForgetpasswordCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    ForgetpasswordCtrl = $controller('ForgetpasswordCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});
