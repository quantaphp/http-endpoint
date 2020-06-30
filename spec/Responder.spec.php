<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;

use Laminas\Diactoros\ResponseFactory;

use Quanta\Http\Responder;
use Quanta\Http\ResponderInterface;

describe('Responder', function () {

    beforeEach(function () {
        $this->responder = new Responder(new ResponseFactory);
    });

    it('should implement ResponderInterface', function () {
        expect($this->responder)->toBeAnInstanceOf(ResponderInterface::class);
    });

    describe('->__invoke()', function () {

        context('when the given response code is 301', function () {

            context('when no body is given', function () {

                it('should return a permament redirect to an empty url', function () {

                    $test = ($this->responder)(301);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(301);
                    expect($test->getHeaderLine('location'))->toEqual('');

                });

            });

            context('when a body is given', function () {

                context('when the given body is a string', function () {

                    it('should return a permament redirect with the given body as url', function () {

                        $test = ($this->responder)(301, '/test');

                        expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                        expect($test->getStatusCode())->toEqual(301);
                        expect($test->getHeaderLine('location'))->toEqual('/test');

                    });

                });

                context('when the given body is not a string', function () {

                    it('should throw an InvalidArgumentException', function () {

                        $test = fn () => ($this->responder)(301, 1);

                        expect($test)->toThrow(new InvalidArgumentException);

                    });

                });

            });

        });

        context('when the given response code is 302', function () {

            context('when no body is given', function () {

                it('should return a temporary redirect to an empty url', function () {

                    $test = ($this->responder)(302);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(302);
                    expect($test->getHeaderLine('location'))->toEqual('');

                });

            });

            context('when a body is given', function () {

                context('when the given body is a string', function () {

                    it('should return a temporary redirect with the given body as url', function () {

                        $test = ($this->responder)(302, '/test');

                        expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                        expect($test->getStatusCode())->toEqual(302);
                        expect($test->getHeaderLine('location'))->toEqual('/test');

                    });

                });

                context('when the given body is not a string', function () {

                    it('should throw an InvalidArgumentException', function () {

                        $test = fn () => ($this->responder)(302, 1);

                        expect($test)->toThrow(new InvalidArgumentException);

                    });

                });

            });

        });

        context('when the given code is neither 301 or 302', function () {

            context('when no body is given', function () {

                it('should return a response with the given code, no body and no content type', function () {

                    $test = ($this->responder)(200);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaders())->toEqual([]);
                    expect((string) $test->getBody())->toEqual('');

                });

            });

            context('when the given body is true', function () {

                it('should return a response with the given code, true as body and application/json as content type', function () {

                    $test = ($this->responder)(200, true);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual('true');

                });

            });

            context('when the given body is false', function () {

                it('should return a response with the given code, false as body and application/json as content type', function () {

                    $test = ($this->responder)(200, false);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual('false');

                });

            });

            context('when the given body is an int', function () {

                it('should return a response with the given code, the given int as body and application/json as content type', function () {

                    $test = ($this->responder)(200, 1);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual('1');

                });

            });

            context('when the given body is a float', function () {

                it('should return a response with the given code, the given float as body and application/json as content type', function () {

                    $test = ($this->responder)(200, 1.1);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual('1.1');

                });

            });

            context('when the given body is an empty string', function () {

                it('should return a response with the given code, no body and no content type', function () {

                    $test = ($this->responder)(200, '');

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaders())->toEqual([]);
                    expect((string) $test->getBody())->toEqual('');

                });

            });

            context('when the given body is a non empty string', function () {

                it('should return a response with the given code, the given string as body and text/html as content type', function () {

                    $test = ($this->responder)(200, 'test');

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('text/html');
                    expect((string) $test->getBody())->toEqual('test');

                });

            });

            context('when the given body is an array', function () {

                it('should return a response with the given code, the given array as body and text/html as content type', function () {

                    $data = ['k1' => 'v1', 'k2' => 'v2'];

                    $test = ($this->responder)(200, $data);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual(json_encode($data));

                });

            });

            context('when the given body is an object', function () {

                it('should return a response with the given code, the given object as body and text/html as content type', function () {

                    $data = new class {
                        public $k1 = 'v1';
                        public $k2 = 'v2';
                    };

                    $test = ($this->responder)(200, $data);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual(json_encode($data));

                });

            });

            context('when the given body is a resource', function () {

                it('should throw an InvalidArgumentException', function () {

                    $test = fn () => ($this->responder)(200, tmpfile());

                    expect($test)->toThrow(new InvalidArgumentException);

                });

            });

            context('when the given body is null', function () {

                it('should return a response with the given code, null as body and text/html as content type', function () {

                    $test = ($this->responder)(200, null);

                    expect($test)->toBeAnInstanceOf(ResponseInterface::class);
                    expect($test->getStatusCode())->toEqual(200);
                    expect($test->getHeaderLine('Content-type'))->toEqual('application/json');
                    expect((string) $test->getBody())->toEqual('null');

                });

            });

        });

    });

});
