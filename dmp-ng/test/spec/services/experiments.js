'use strict';

describe('Service: Experiments', function () {

  // load the service's module
  beforeEach(module('melanomxApp'));

  // instantiate service
  var Experiments;
  beforeEach(inject(function (_Experiments_) {
    Experiments = _Experiments_;
  }));

  it('should do something', function () {
    expect(!!Experiments).toBe(true);
  });

});
