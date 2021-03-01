<?php

declare(strict_types=1);

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Laminas\Diactoros\ResponseFactory;

use Quanta\Http\Input;
use Quanta\Http\Endpoint;

describe('Endpoint', function () {

    beforeEach(function () {
        $this->factory = new ResponseFactory;
        $this->f = stub();
    });

    context('when no json serializer is given', function () {

        beforeEach(function () {
            $this->handler = new Endpoint($this->factory, $this->f);
        });

        it('should be an instance of RequestHandlerInterface', function () {
            expect($this->handler)->toBeAnInstanceOf(RequestHandlerInterface::class);
        });

        describe('->handle()', function () {

            beforeEach(function () {
                $this->request = mock(ServerRequestInterface::class);

                $this->input = new Input($this->request->get());
                $this->responder = [$this->factory, 'createResponse'];
            });

            context('when the callable returns true', function () {

                it('should return a 200 json response with true as body', function () {
                    $this->f->with($this->input, $this->responder)->returns(true);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual(json_encode(true));
                });

            });

            context('when the callable returns false', function () {

                it('should return a 200 json response with false as body', function () {
                    $this->f->with($this->input, $this->responder)->returns(false);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual(json_encode(false));
                });

            });

            context('when the callable returns an int', function () {

                it('should return a 200 json response with the int as body', function () {
                    $this->f->with($this->input, $this->responder)->returns(1);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual(json_encode(1));
                });

            });

            context('when the callable returns a float', function () {

                it('should return a 200 json response with the float as body', function () {
                    $this->f->with($this->input, $this->responder)->returns(1.1);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual(json_encode(1.1));
                });

            });

            context('when the callable returns a string', function () {

                it('should return a 200 html response with the string as body', function () {
                    $this->f->with($this->input, $this->responder)->returns('test');

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('text/html');
                    expect((string) $test->getBody())->toEqual('test');
                });

            });

            context('when the callable returns an array', function () {

                it('should return a 200 json response with the array as body', function () {
                    $data = ['k1' => 'v1', 'k2' => 'v2'];

                    $this->f->with($this->input, $this->responder)->returns($data);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual(json_encode($data));
                });

            });

            context('when the callable returns an object', function () {

                context ('when the object implements ResponseInterface', function () {

                    it('should return the response', function () {
                        $response = mock(ResponseInterface::class);

                        $this->f->with($this->input, $this->responder)->returns($response);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBe($response->get());
                    });

                });

                context ('when the object implements Traversable', function () {

                    it('should return a 200 json response with the converted traversable as body', function () {
                        $data = ['k1' => 'v1', 'k2' => 'v2'];

                        $this->f->with($this->input, $this->responder)->returns(new ArrayIterator($data));

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                        expect($test->getStatusCode())->toEqual(200);
                        expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                        expect((string) $test->getBody())->toEqual(json_encode($data));
                    });

                });

                context ('when the object does not implement neither ResponseInterface nor Traversable', function () {

                    it('should return a 200 json response with the object as body', function () {
                        $object = new class {
                            public $k1 = 'v1';
                            public $k2 = 'v2';
                        };

                        $this->f->with($this->input, $this->responder)->returns($object);

                        $test = $this->handler->handle($this->request->get());

                        expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                        expect($test->getStatusCode())->toEqual(200);
                        expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                        expect((string) $test->getBody())->toEqual(json_encode($object));
                    });

                });

            });

            context('when the callable returns a resource', function () {

                it('should throw an Exception wrapped around the JsonException', function () {
                    $resource = tmpfile();

                    $this->f->with($this->input, $this->responder)->returns($resource);

                    $test = fn () => $this->handler->handle($this->request->get());

                    expect($test)->toThrow(new Exception);

                    try {
                        $test();
                    }

                    catch (\Throwable $e) {
                        expect($e->getPrevious())->toBeAnInstanceOf(JsonException::class);
                    }
                });

            });

            context('when the callable returns null', function () {

                it('should return a 404 empty response', function () {
                    $this->f->with($this->input, $this->responder)->returns(null);

                    $test = $this->handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(404);
                    expect($test->getHeaders())->toEqual([]);
                    expect((string) $test->getBody())->toEqual('');
                });

            });

        });

    });

    context('when a json serializer is given', function () {

        describe('->handle()', function () {

            beforeEach(function () {
                $this->request = mock(ServerRequestInterface::class);
            });

            context('when the callable returns a string', function () {

                it('should return a 200 html response with the string as body', function () {
                    $handler = new Endpoint($this->factory, $this->f, fn () => 'any');

                    $this->f->returns('test');

                    $test = $handler->handle($this->request->get());

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('text/html');
                    expect((string) $test->getBody())->toEqual('test');
                });

            });

            context('when the callable returns a ResponseInterface', function () {

                it('it should return the response', function () {
                    $response = mock(ResponseInterface::class);

                    $handler = new Endpoint($this->factory, $this->f, fn () => 'any');

                    $this->f->returns($response);

                    $test = $handler->handle($this->request->get());

                    expect($test)->toBe($test);
                });

            });

            context('when the callable does not return a string or a ResponseInterface', function () {

                context('when the json serializer returns a string', function () {

                    it('should return a 200 json response with the string as body', function () {
                        $serializer = stub()->with(1)->returns('test');

                        $handler = new Endpoint($this->factory, $this->f, $serializer);

                        $this->f->returns(1);

                        $test = $handler->handle($this->request->get());

                        expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                        expect($test->getStatusCode())->toEqual(200);
                        expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                        expect((string) $test->getBody())->toEqual('test');
                    });

                });

                context('when the json serializer expects more than 1 argument', function () {

                    it ('it should throw a LogicException wrapped around the ArgumentCountError', function () {
                        $serializer = fn ($a, $b) => 'test';

                        $handler = new Endpoint($this->factory, $this->f, $serializer);

                        $this->f->returns(1);

                        $test = fn () => $handler->handle($this->request->get());

                        expect($test)->toThrow(new LogicException);

                        try {
                            $test();
                        }

                        catch (\LogicException $e) {
                            expect($e->getPrevious())->toBeAnInstanceOf(ArgumentCountError::class);
                        }
                    });

                });

                context('when the json serializer throws a TypeError', function () {

                    context('when the json serializer argument is not mixed', function () {

                        it ('it should throw a LogicException wrapped around the InvalidArgumentException', function () {
                            $serializer = fn (array $a) => 'test';

                            $handler = new Endpoint($this->factory, $this->f, $serializer);

                            $this->f->returns(1);

                            $test = fn () => $handler->handle($this->request->get());

                            expect($test)->toThrow(new LogicException);

                            try {
                                $test();
                            }

                            catch (\LogicException $e) {
                                expect($e->getPrevious())->toBeAnInstanceOf(TypeError::class);
                            }
                        });

                    });

                    context('when the json serializer argument has no type', function () {

                        it ('it should throw an Exception wrapped around the TypeError', function () {
                            $exception = new TypeError;

                            $serializer = function ($a) use ($exception) { throw $exception; };

                            $handler = new Endpoint($this->factory, $this->f, $serializer);

                            $this->f->returns(1);

                            $test = fn () => $handler->handle($this->request->get());

                            expect($test)->toThrow(new Exception);

                            try {
                                $test();
                            }

                            catch (\Throwable $e) {
                                expect($e->getPrevious())->toBe($exception);
                            }
                        });

                    });

                    if (version_compare(phpversion(), '8.0.0', '>=')) {

                        context('when the json serializer argument is mixed', function () {

                            it ('it should throw an Exception wrapped around the TypeError', function () {
                                $exception = new TypeError;

                                $serializer = function (mixed $a) use ($exception) { throw $exception; };

                                $handler = new Endpoint($this->factory, $this->f, $serializer);

                                $this->f->returns(1);

                                $test = fn () => $handler->handle($this->request->get());

                                expect($test)->toThrow(new Exception);

                                try {
                                    $test();
                                }

                                catch (\Throwable $e) {
                                    expect($e->getPrevious())->toBe($exception);
                                }
                            });

                        });

                    }

                });

                context('when the json serializer throws any other exception', function () {

                    it ('it should throw an Exception wrapped around the TypeError', function () {
                        $exception = new Exception;

                        $serializer = function ($a) use ($exception) { throw $exception; };

                        $handler = new Endpoint($this->factory, $this->f, $serializer);

                        $this->f->returns(1);

                        $test = fn () => $handler->handle($this->request->get());

                        expect($test)->toThrow(new Exception);

                        try {
                            $test();
                        }

                        catch (\Throwable $e) {
                            expect($e->getPrevious())->toBe($exception);
                        }
                    });

                });

                context('when the json serializer does not return a string', function () {

                    it ('it should throw an UnexpectedValueException', function () {
                        $serializer = fn ($a) => 1;

                        $handler = new Endpoint($this->factory, $this->f, $serializer);

                        $this->f->returns(1);

                        $test = fn () => $handler->handle($this->request->get());

                        expect($test)->toThrow(new UnexpectedValueException);
                    });

                });

            });

        });

    });

});
