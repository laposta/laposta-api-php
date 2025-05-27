<?php

declare(strict_types=1);

namespace LapostaApi\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    /**
     * Constructor for the Request object
     *
     * @param string $method HTTP method (e.g., GET, POST, etc.)
     * @param UriInterface $uri The URI of the request.
     * @param StreamInterface $body The request body (optional).
     * @param array $headers Associative array with headers.
     * @param string|null $requestTarget Target of the request (optional).
     */
    public function __construct(
        protected string $method,
        protected UriInterface $uri,
        protected StreamInterface $body,
        array $headers = [],
        protected ?string $requestTarget = null,
    ) {
        $this->method = strtoupper($method);
        $this->validateMethod($this->method);

        // Set and normalize headers
        $normalizedHeaders = [];
        foreach ($headers as $name => $value) {
            $normalizedHeaders[$this->normalizeHeaderName($name)] = $value;
        }
        $this->headers = $normalizedHeaders;

        // Generate request target if not provided
        $this->requestTarget = $requestTarget ?? $this->generateRequestTarget();

        // Set Host header based on URI (if available)
        $this->updateHostFromUri();
    }

    /**
     * Generate the default request target from the URI.
     */
    protected function generateRequestTarget(): string
    {
        $path = $this->uri->getPath() ?: '/';
        $query = $this->uri->getQuery();

        return $query ? $path . '?' . $query : $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        if (preg_match('/\s/', $requestTarget)) {
            throw new \InvalidArgumentException('Invalid request target; it must not contain whitespace.');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod(string $method): RequestInterface
    {
        $method = strtoupper($method);
        $this->validateMethod($method);

        $new = clone $this;
        $new->method = strtoupper($method);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost) {
            $new->updateHostFromUri();
        }

        return $new;
    }

    /**
     * Update the `Host` header based on the URI
     */
    protected function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();
        if ($host === '') {
            return;
        }

        if ($port = $this->uri->getPort()) {
            $host .= ':' . $port;
        }

        $this->headers['host'] = [$host];
    }

    /**
     * Validate an HTTP method.
     *
     * @param string $method The HTTP method to validate.
     *
     * @throws \InvalidArgumentException If the method is invalid.
     */
    protected function validateMethod(string $method): void
    {
        $validMethods = [
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'HEAD',
            'OPTIONS',
            'PATCH',
            'TRACE',
            'CONNECT',
        ];

        if (!in_array($method, $validMethods, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid HTTP method provided: "%s".', $method));
        }
    }
}
