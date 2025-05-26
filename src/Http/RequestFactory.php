<?php

declare(strict_types=1);

namespace LapostaApi\Http;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface
{
    public function __construct(
        protected StreamFactoryInterface $streamFactory = new StreamFactory(),
        protected UriFactoryInterface $uriFactory = new UriFactory(),
    ) {
    }


    /**
     * Create a new HTTP request with optional extra headers.
     *
     * @param string $method The HTTP method (e.g., 'GET', 'POST').
     * @param UriInterface|string $uri The URI for the request as a string or UriInterface.
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        // Convert URI string to UriInterface if necessary
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        // Check $uri type
        if (!$uri instanceof UriInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The $uri argument must be a string or an instance of %s. %s given.',
                    UriInterface::class,
                    get_debug_type($uri),
                ),
            );
        }

        // Create the Request object
        $request = new Request($method, $uri, $this->streamFactory->createStream());

        return $request;
    }
}
