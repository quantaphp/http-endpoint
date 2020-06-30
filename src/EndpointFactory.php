<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ResponseFactoryInterface;

final class EndpointFactory
{
    /**
     * @var \Quanta\Http\ResponderInterface
     */
    private $responder;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var array<string, mixed>
     */
    private array $metadata;

    /**
     * Return an EndpointFactory using the default responder.
     *
     * @param \Psr\Http\Message\ResponseFactoryInterface    $factory
     * @param string                                        $key
     * @param array<string, mixed>                          $metadata
     * @return \Quanta\Http\EndpointFactory
     */
    public static function default(
        ResponseFactoryInterface $factory,
        string $key = Endpoint::DEFAULT_KEY,
        array $metadata = Endpoint::DEFAULT_METADATA
    ): self {
        return new self(new Responder($factory), $key, $metadata);
    }

    /**
     * @param \Quanta\Http\ResponderInterface   $responder
     * @param string                            $key
     * @param array<string, mixed>              $metadata
     */
    public function __construct(
        ResponderInterface $responder,
        string $key = Endpoint::DEFAULT_KEY,
        array $metadata = Endpoint::DEFAULT_METADATA
    ) {
        $this->responder = $responder;
        $this->key = $key;
        $this->metadata = $metadata;
    }

    /**
     * @param callable(\Psr\Http\Message\ServerRequestInterface, \Quanta\Http\ResponderInterface): mixed $f
     * @return \Quanta\Http\Endpoint
     */
    public function __invoke(callable $f): Endpoint
    {
        return new Endpoint($this->responder, $f, $this->key, $this->metadata);
    }
}
