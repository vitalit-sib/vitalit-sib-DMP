'use strict';

describe('Directive: access', function () {

  // load the directive's module
  beforeEach(module('melanomxApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<access></access>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the access directive');
  }));
});
