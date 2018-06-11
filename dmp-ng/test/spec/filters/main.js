'use strict';

describe('Filter: main', function () {

  // load the filter's module
  beforeEach(module('melanomxApp'));

  // initialize a new instance of the filter before each test
  var main;
  beforeEach(inject(function ($filter) {
    main = $filter('main');
  }));

  it('should return the input prefixed with "main filter:"', function () {
    var text = 'angularjs';
    expect(main(text)).toBe('main filter: ' + text);
  });

});
