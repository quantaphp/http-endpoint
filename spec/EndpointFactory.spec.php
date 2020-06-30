<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ResponseFactoryInterface;

use Quanta\Http\Endpoint;
use Quanta\Http\Responder;
use Quanta\Http\EndpointFactory;
use Quanta\Http\ResponderInterface;

describe('Endpoint::default()', function () {

    beforeEach(function () {
        $this->factory = mock(ResponseFactoryInterface::class);
    });

    context('when no key and metadata are given', function () {

        it('should return an EndpointFactory using the default responder, key and metadata', function () {
            $test = EndpointFactory::default($this->factory->get());

            expect($test)->toEqual(new EndpointFactory(
                new Responder($this->factory->get())
            ));
        });

    });

    context('when key and metadata are given', function () {

        it('should return an EndpointFactory using the default responder, the given key and metadata', function () {
            $test = EndpointFactory::default($this->factory->get(), 'key', ['m' => 'v']);

            expect($test)->toEqual(new EndpointFactory(
                new Responder($this->factory->get()),
                'key',
                ['m' => 'v'],
            ));
        });

    });

});

describe('EndpointFactory', function () {

    beforeEach(function () {
        $this->responder = mock(ResponderInterface::class);
    });

    context('when no key and metadata are given', function () {

        beforeEach(function () {
            $this->factory = new EndpointFactory($this->responder->get());
        });

        describe('->__invoke()', function () {

            it('should return an Endpoint using the given callable, the responder, default key and metadata', function () {
                $f = stub();

                $test = ($this->factory)($f);

                expect($test)->toEqual(new Endpoint(
                    $this->responder->get(),
                    $f,
                ));
            });

        });

    });

    context('when key and metadata are given', function () {

        beforeEach(function () {
            $this->factory = new EndpointFactory($this->responder->get(), 'key', ['m' => 'v']);
        });

        describe('->__invoke()', function () {

            it('should return an Endpoint using the given callable, the responder, the key and metadata', function () {
                $f = stub();

                $test = ($this->factory)($f);

                expect($test)->toEqual(new Endpoint(
                    $this->responder->get(),
                    $f,
                    'key',
                    ['m' => 'v'],
                ));
            });

        });

    });

});
