<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Endpoint implements RequestHandlerInterface
{
    /**
     * @var string
     */
    public const DEFAULT_KEY = 'data';

    /**
     * @var array<string, mixed>
     */
    public const DEFAULT_METADATA = ['code' => 200, 'success' => true];

    /**
     * @var callable(int, string|mixed[]): \Psr\Http\Message\ResponseInterface
     */
    private $responder;

    /**
     * @var callable(callable, callable): mixed
     */
    private $f;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var array<string, mixed>
     */
    private array $metadata;

    /**
     * @param callable(int, string|mixed[]): \Psr\Http\Message\ResponseInterface    $responder
     * @param callable(callable, callable): mixed                                   $f
     * @param string                                                                $key
     * @param array<string, mixed>                                                  $metadata
     */
    public function __construct(callable $responder, callable $f, string $key = self::DEFAULT_KEY, array $metadata = self::DEFAULT_METADATA)
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
        $result = ($this->f)(new Input($request), $this->responder);

        if (is_null($result)) {
            return ($this->responder)(200, '');
        }

        if ($result === false) {
            return ($this->responder)(404, '');
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
