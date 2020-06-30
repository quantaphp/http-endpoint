<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Quanta\Http\Endpoint;
use Quanta\Http\ResponderInterface;

describe('Endpoint', function () {

    beforeEach(function () {
        $this->responder = mock(ResponderInterface::class);
        $this->f = stub();
    });

    context('when no key and metadata are given', function () {

        beforeEach(function () {
            $this->handler = new Endpoint(
                $this->responder->get(),
                $this->f,
            );
        });

        it('should be an instance of RequestHandlerInterface', function () {
            expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);
        });

        describe('->handle()', function () {

            beforeEach(function () {
                $this->request = mock(ServerRequestInterface::class);
                $this->response = mock(ResponseInterface::class);
            });

            context('when the function returns true', function () {

                it('should call the responder with 200 and an array with true as data', function () {
                    $this->f->with($this->request, $this->responder)->returns(true);

                    $this->responder->__invoke
                        ->with(200, Endpoint::DEFAULT_METADATA + [Endpoint::DEFAULT_KEY => true])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns false', function () {

                it('should call the responder with 404', function () {
                    $this->f->with($this->request, $this->responder)->returns(false);

                    $this->responder->__invoke
                        ->with(404)
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns an int', function () {

                it('should call the responder with 200 and an array with the int as data', function () {
                    $this->f->with($this->request, $this->responder)->returns(1);

                    $this->responder->__invoke
                        ->with(200, Endpoint::DEFAULT_METADATA + [Endpoint::DEFAULT_KEY => 1])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns a float', function () {

                it('should call the responder with 200 and an array with the float as data', function () {
                    $this->f->with($this->request, $this->responder)->returns(1.1);

                    $this->responder->__invoke
                        ->with(200, Endpoint::DEFAULT_METADATA + [Endpoint::DEFAULT_KEY => 1.1])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns a string', function () {

                it('should call the responder with 200 and the string', function () {
                    $this->f->with($this->request, $this->responder)->returns('test');

                    $this->responder->__invoke
                        ->with(200, 'test')
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns an array', function () {

                it('should call the responder with 200 and an array with the array as data', function () {
                    $data = ['k1' => 'v1', 'k2' => 'v2'];

                    $this->f->with($this->request, $this->responder)->returns($data);

                    $this->responder->__invoke
                        ->with(200, Endpoint::DEFAULT_METADATA + [Endpoint::DEFAULT_KEY => $data])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns an object', function () {

                context ('when the object implements ResponseInterface', function () {

                    it('should return the response', function () {
                        $this->f->with($this->request, $this->responder)->returns($this->response);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBe($this->response->get());
                    });

                });

                context ('when the object implements Traversable', function () {

                    it('should call the responder with 200 and an array with the converted traversable as data', function () {
                        $data = ['k1' => 'v1', 'k2' => 'v2'];

                        $this->f->with($this->request, $this->responder)->returns(new ArrayIterator($data));

                        $this->responder->__invoke
                            ->with(200, Endpoint::DEFAULT_METADATA + [Endpoint::DEFAULT_KEY => $data])
                            ->returns($this->response);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBe($this->response->get());
                    });

                });

                context ('when the object does not implement neither ResponseInterface nor Traversable', function () {

                    it('should call the responder with 200 and an array with the object as data', function () {
                        $data = new class {
                            public $k1 = 'v1';
                            public $k2 = 'v2';
                        };

                        $this->f->with($this->request, $this->responder)->returns($data);

                        $this->responder->__invoke
                            ->with(200, Endpoint::DEFAULT_METADATA + [Endpoint::DEFAULT_KEY => $data])
                            ->returns($this->response);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBe($this->response->get());
                    });

                });

            });

            context('when the function returns a resource', function () {

                it('should call the responder with 200 and an array with the resource as data', function () {
                    $data = tmpfile();

                    $this->f->with($this->request, $this->responder)->returns($data);

                    $this->responder->__invoke
                        ->with(200, Endpoint::DEFAULT_METADATA + [Endpoint::DEFAULT_KEY => $data])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns null', function () {

                it('should call the responder with 200', function () {
                    $this->f->with($this->request, $this->responder)->returns(null);

                    $this->responder->__invoke
                        ->with(200)
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

        });

    });

    context('when key and metadata are given', function () {

        beforeEach(function () {
            $this->handler = new Endpoint(
                $this->responder->get(),
                $this->f,
                'key',
                ['m' => 'v'],
            );
        });

        it('should be an instance of RequestHandlerInterface', function () {
            expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);
        });

        describe('->handle()', function () {

            beforeEach(function () {
                $this->request = mock(ServerRequestInterface::class);
                $this->response = mock(ResponseInterface::class);
            });

            context('when the function returns true', function () {

                it('should call the responder with 200 and an array with true as data', function () {
                    $this->f->with($this->request, $this->responder)->returns(true);

                    $this->responder->__invoke
                        ->with(200, ['m' =>'v', 'key' => true])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns false', function () {

                it('should call the responder with 404', function () {
                    $this->f->with($this->request, $this->responder)->returns(false);

                    $this->responder->__invoke
                        ->with(404)
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns an int', function () {

                it('should call the responder with 200 and an array with the int as data', function () {
                    $this->f->with($this->request, $this->responder)->returns(1);

                    $this->responder->__invoke
                        ->with(200, ['m' =>'v', 'key' => 1])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns a float', function () {

                it('should call the responder with 200 and an array with the float as data', function () {
                    $this->f->with($this->request, $this->responder)->returns(1.1);

                    $this->responder->__invoke
                        ->with(200, ['m' =>'v', 'key' => 1.1])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns a string', function () {

                it('should call the responder with 200 and the string', function () {
                    $this->f->with($this->request, $this->responder)->returns('test');

                    $this->responder->__invoke
                        ->with(200, 'test')
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns an array', function () {

                it('should call the responder with 200 and an array with the array as data', function () {
                    $data = ['k1' => 'v1', 'k2' => 'v2'];

                    $this->f->with($this->request, $this->responder)->returns($data);

                    $this->responder->__invoke
                        ->with(200, ['m' =>'v', 'key' => $data])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns an object', function () {

                context ('when the object implements ResponseInterface', function () {

                    it('should return the response', function () {
                        $this->f->with($this->request, $this->responder)->returns($this->response);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBe($this->response->get());
                    });

                });

                context ('when the object implements Traversable', function () {

                    it('should call the responder with 200 and an array with the converted traversable as data', function () {
                        $data = ['k1' => 'v1', 'k2' => 'v2'];

                        $this->f->with($this->request, $this->responder)->returns(new ArrayIterator($data));

                        $this->responder->__invoke
                            ->with(200, ['m' =>'v', 'key' => $data])
                            ->returns($this->response);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBe($this->response->get());
                    });

                });

                context ('when the object does not implement neither ResponseInterface nor Traversable', function () {

                    it('should call the responder with 200 and an array with the object as data', function () {
                        $data = new class {
                            public $k1 = 'v1';
                            public $k2 = 'v2';
                        };

                        $this->f->with($this->request, $this->responder)->returns($data);

                        $this->responder->__invoke
                            ->with(200, ['m' =>'v', 'key' => $data])
                            ->returns($this->response);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBe($this->response->get());
                    });

                });

            });

            context('when the function returns a resource', function () {

                it('should call the responder with 200 and an array with the resource as data', function () {
                    $data = tmpfile();

                    $this->f->with($this->request, $this->responder)->returns($data);

                    $this->responder->__invoke
                        ->with(200, ['m' =>'v', 'key' => $data])
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

            context('when the function returns null', function () {

                it('should call the responder with 200', function () {
                    $this->f->with($this->request, $this->responder)->returns(null);

                    $this->responder->__invoke
                        ->with(200)
                        ->returns($this->response);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBe($this->response->get());
                });

            });

        });

    });

});
