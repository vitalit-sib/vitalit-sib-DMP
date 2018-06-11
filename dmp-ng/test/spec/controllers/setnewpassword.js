'use strict';

describe('Controller: SetnewpasswordCtrl', function () {

  // load the controller's module
  beforeEach(module('melanomxApp'));

  var SetnewpasswordCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    SetnewpasswordCtrl = $controller('SetnewpasswordCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});
