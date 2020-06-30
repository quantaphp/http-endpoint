<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ResponseInterface;

interface ResponderInterface
{
    /**
     * @param int   $code
     * @param mixed $body
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(int $code, $body = ''): ResponseInterface;
}
