<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ServerRequestInterface;

final class MetadataSerializer
{
    /**
     * @var string
     */
    private string $key;

    /**
     * @var array<mixed>
     */
    private array $metadata;

    /**
     * @param string        $key
     * @param array<mixed>  $metadata
     */
    public function __construct(string $key, array $metadata)
    {
        $this->key = $key;
        $this->metadata = $metadata;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function __invoke($value): string
    {
        $wrapped = array_merge($this->metadata, [
            $this->key => $value instanceof \Traversable
                ? iterator_to_array($value)
                : $value
        ]);

        return json_encode($wrapped, JSON_THROW_ON_ERROR);
    }
}
