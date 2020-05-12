<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Endpoint implements RequestHandlerInterface
{
    /**
     * @var callable(int, mixed = ''): \Psr\Http\Message\ResponseInterface
     */
    private $responder;

    /**
     * @var callable(ServerRequestInterface, callable(int, mixed): \Psr\Http\Message\ResponseInterface): mixed
     */
    private $f;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array<string, mixed>
     */
    private $metadata;

    /**
     * @param callable(int, mixed): \Psr\Http\Message\ResponseInterface                                             $responder
     * @param callable(ServerRequestInterface, callable(int, mixed): \Psr\Http\Message\ResponseInterface): mixed    $f
     * @param string                                                                                                $key
     * @param array<string, mixed>                                                                                  $metadata
     */
    public function __construct(callable $responder, callable $f, string $key = 'data', array $metadata = [])
    {
        $this->responder = $responder;
        $this->f = $f;
        $this->key = $key;
        $this->metadata = $metadata;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = ($this->f)($request, $this->responder);

        if (is_null($result)) {
            return ($this->responder)(200);
        }

        if ($result === false) {
            return ($this->responder)(404);
        }

        if (is_string($result)) {
            return ($this->responder)(200, $result);
        }

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $data = array_merge($this->metadata, [
            $this->key => $result instanceof \Traversable
                ? iterator_to_array($result)
                : $result,
        ]);

        return ($this->responder)(200, $data);
    }
}
