<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Endpoint implements RequestHandlerInterface
{
    /**
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    private $factory;

    /**
     * @var callable(callable, callable): mixed
     */
    private $f;

    /**
     * @var null|callable(mixed): string
     */
    private $serializer;

    /**
     * @param \Psr\Http\Message\ResponseFactoryInterface    $factory
     * @param callable(callable, callable): mixed           $f
     * @param callable                                      $serializer
     */
    public function __construct(ResponseFactoryInterface $factory, callable $f, callable $serializer = null)
    {
        $this->factory = $factory;
        $this->f = $f;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $value = ($this->f)(new Input($request), [$this->factory, 'createResponse']);

        if (is_null($value)) {
            return $this->factory->createResponse(404);
        }

        if (is_string($value)) {
            return $this->html($value);
        }

        if  (is_array($value)) {
            return $this->json($value);
        }

        return $value instanceof ResponseInterface ? $value : $this->json($value);
    }

    /**
     * @param string $contents
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function html(string $contents): ResponseInterface
    {
        $response = $this->factory->createResponse(200);

        $response->getBody()->write($contents);

        return $response->withHeader('content-type', 'text/html');
    }

    /**
     * @param mixed $value
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function json($value): ResponseInterface
    {
        $contents = $this->serialize($value);

        $response = $this->factory->createResponse(200);

        $response->getBody()->write($contents);

        return $response->withHeader('content-type', 'application/json');
    }

    /**
     * @param mixed $value
     * @return string
     * @throws \Exception
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function serialize($value): string
    {
        // Default error message wrapper.
        $err = 'Error while serializing the response contents as json';

        // default serializer.
        if (is_null($this->serializer)) {
            try {
                return json_encode($value, JSON_THROW_ON_ERROR);
            }

            catch (\Throwable $e) {
                throw new \Exception($err, 0, $e);
            }
        }

        // custom serializer.
        try {
            $contents = ($this->serializer)($value);
        }

        catch (\ArgumentCountError $e) {
            throw new \LogicException('The json serializer must expect only one argument', 0, $e);
        }

        catch (\TypeError $e) {
            $serializer = \Closure::fromCallable($this->serializer);

            $reflection = new \ReflectionFunction($serializer);

            $parameters = $reflection->getParameters();

            /** @var \ReflectionNamedType|null */
            $type = $parameters[0]->getType();

            if (is_null($type) || $type->getName() == 'mixed') {
                throw new \Exception($err, 0, $e);
            }

            throw new \LogicException('The first argument of the json serializer must accept any type', 0, $e);
        }

        catch (\Throwable $e) {
            throw new \Exception($err, 0, $e);
        }

        if (!is_string($contents)) {
            throw new \UnexpectedValueException(
                sprintf('The json serializer must return a string, %s returned', gettype($contents))
            );
        }

        return $contents;
    }
}
