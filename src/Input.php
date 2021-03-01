<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ServerRequestInterface;

final class Input
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param string    $key
     * @param mixed     ...$xs
     * @return mixed
     * @throws \Exception
     */
    public function __invoke(string $key = '', ...$xs)
    {
        if ($key == '') {
            return $this->request;
        }

        $attributes = $this->request->getAttributes();

        if (array_key_exists($key, $attributes)) {
            return $attributes[$key];
        }

        $body = $this->request->getParsedBody();

        if (is_array($body) && array_key_exists($key, $body)) {
            return $body[$key];
        }

        $query = $this->request->getQueryParams();

        if (array_key_exists($key, $query)) {
            return $query[$key];
        }

        if (count($xs) > 0) {
            return $xs[0];
        }

        throw new \Exception(sprintf('input \'%s\' not found', $key));
    }
}
