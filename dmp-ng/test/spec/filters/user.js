'use strict';

describe('Filter: user', function () {

  // load the filter's module
  beforeEach(module('melanomxApp'));

  // initialize a new instance of the filter before each test
  var user;
  beforeEach(inject(function ($filter) {
    user = $filter('user');
  }));

  it('should return the input prefixed with "user filter:"', function () {
    var text = 'angularjs';
    expect(user(text)).toBe('user filter: ' + text);
  });

});
