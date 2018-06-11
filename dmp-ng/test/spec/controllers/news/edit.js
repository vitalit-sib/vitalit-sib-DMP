'use strict';

describe('Controller: NewsEditCtrl', function () {

  // load the controller's module
  beforeEach(module('melanomxApp'));

  var NewsEditCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    NewsEditCtrl = $controller('NewsEditCtrl', {
      $scope: scope
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(scope.awesomeThings.length).toBe(3);
  });
});
