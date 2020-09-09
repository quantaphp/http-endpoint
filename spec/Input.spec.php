<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;

use Quanta\Http\Input;

describe('Input', function () {

    beforeEach(function () {
        $this->request = mock(ServerRequestInterface::class);

        $this->input = new Input($this->request->get());
    });

    describe('->__invoke()', function () {

        context('when no argument is given', function () {

            it('should return the request', function () {
                $test = ($this->input)();

                expect($test)->toBe($this->request->get());
            });

        });

        context('when a key is given', function () {

            context('when the request attributes has the given key', function () {

                it('should return the attribute value', function () {
                    $this->request->getAttributes->returns(['key' => 'value1']);
                    $this->request->getParsedBody->returns(['key' => 'value2']);
                    $this->request->getQueryParams->returns(['key' => 'value3']);

                    $test = ($this->input)('key');

                    expect($test)->toEqual('value1');
                });

            });

            context('when the request body has the given key', function () {

                it('should return the body value', function () {
                    $this->request->getAttributes->returns([]);
                    $this->request->getParsedBody->returns(['key' => 'value1']);
                    $this->request->getQueryParams->returns(['key' => 'value2']);

                    $test = ($this->input)('key');

                    expect($test)->toEqual('value1');
                });

            });

            context('when the request query has the given key', function () {

                it('should return the query value', function () {
                    $this->request->getAttributes->returns([]);
                    $this->request->getParsedBody->returns([]);
                    $this->request->getQueryParams->returns(['key' => 'value1']);

                    $test = ($this->input)('key');

                    expect($test)->toEqual('value1');
                });

            });

            context('when the given key is not found', function () {

                context('when a default value is given', function () {

                    it('should return the given default value', function () {
                        $this->request->getAttributes->returns([]);
                        $this->request->getParsedBody->returns([]);
                        $this->request->getQueryParams->returns([]);

                        $test = ($this->input)('key', 'default');

                        expect($test)->toEqual('default');
                    });

                });

                context('when no default value is given', function () {

                    it('should throw an exception', function () {
                        $this->request->getAttributes->returns([]);
                        $this->request->getParsedBody->returns([]);
                        $this->request->getQueryParams->returns([]);

                        $test = fn () => ($this->input)('key');

                        expect($test)->toThrow();
                    });

                });

            });

        });

    });

});
