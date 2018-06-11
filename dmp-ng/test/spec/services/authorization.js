'use strict';

describe('Service: authorization', function () {

  // load the service's module
  beforeEach(module('melanomxApp'));

  // instantiate service
  var authorization;
  beforeEach(inject(function (_authorization_) {
    authorization = _authorization_;
  }));

  it('should do something', function () {
    expect(!!authorization).toBe(true);
  });

});
