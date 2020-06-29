<?php

declare(strict_types=1);

namespace Quanta\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class Responder
{
    /**
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    private ResponseFactoryInterface $factory;

    /**
     * @var int
     */
    private int $options;

    /**
     * @var int
     */
    private int $depth;

    /**
     * @param \Psr\Http\Message\ResponseFactoryInterface    $factory
     * @param int                                           $options
     * @param int                                           $depth
     */
    public function __construct(ResponseFactoryInterface $factory, int $options = 0, int $depth = 512)
    {
        $this->factory = $factory;
        $this->options = $options;
        $this->depth = $depth;
    }

    /**
     * Return a response according to the given code and body.
     *
     * @param int   $code
     * @param mixed $body
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function __invoke(int $code, $body = ''): ResponseInterface
    {
        if ($code == 301 || $code == 302) {
            if (is_string($body)) {
                return $this->factory->createResponse($code)
                    ->withHeader('Location', $body);
            }

            throw new \InvalidArgumentException('Redirect responses (301 and 302 codes) must have a string body (the url)');
        }

        if (is_string($body)) {
            return $this->response($code, $body, 'text/html');
        }

        try {
            $body = json_encode($body, $this->options | JSON_THROW_ON_ERROR, $this->depth);
        }

        catch (\JsonException $e) {
            throw new \InvalidArgumentException('The given body can\'t be encoded as json');
        }

        if ($body === false) throw new \Exception;

        return $this->response($code, $body, 'application/json');
    }

    /**
     * Return a response from the given code, body string and content type.
     *
     * Content type header is ommited when the body string is empty.
     *
     * @param int       $code
     * @param string    $body
     * @param string    $type
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function response(int $code, string $body, string $type): ResponseInterface
    {
        $response = $this->factory->createResponse($code);

        if (!empty($body)) {
            $response = $response->withHeader('Content-type', $type);

            $response->getBody()->write($body);
        }

        return $response;
    }
}
