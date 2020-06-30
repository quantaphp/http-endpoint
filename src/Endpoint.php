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
     * @var \Quanta\Http\ResponderInterface
     */
    private ResponderInterface $responder;

    /**
     * @var callable(\Psr\Http\Message\ServerRequestInterface, \Quanta\Http\ResponderInterface): mixed
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
     * @param \Quanta\Http\ResponderInterface                                                               $responder
     * @param callable(\Psr\Http\Message\ServerRequestInterface, \Quanta\Http\ResponderInterface): mixed    $f
     * @param string                                                                                        $key
     * @param array<string, mixed>                                                                          $metadata
     */
    public function __construct(
        ResponderInterface $responder,
        callable $f,
        string $key = self::DEFAULT_KEY,
        array $metadata = self::DEFAULT_METADATA
    ) {
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
