'use strict';

describe('Controller: ValidationrequiredCtrl', function () {

  // load the controller's module
  beforeEach(module('melanomxApp'));

  var ValidationrequiredCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    ValidationrequiredCtrl = $controller('ValidationrequiredCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});
