<?php

declare(strict_types=1);

namespace LapostaApi\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    /**
     * @var string HTTP protocol version
     */
    protected string $protocolVersion = '1.1';

    /**
     * @var array<string, array<string>> Headers as key => value[]
     */
    protected array $headers = [];

    /**
     * @var StreamInterface Body of the HTTP message
     */
    protected StreamInterface $body;

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader(string $name): bool
    {
        $normalized = $this->normalizeHeaderName($name);
        return isset($this->headers[$normalized]);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader(string $name): array
    {
        $normalized = $this->normalizeHeaderName($name);
        return $this->headers[$normalized] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine(string $name): string
    {
        $normalized = $this->normalizeHeaderName($name);
        if (!isset($this->headers[$normalized])) {
            return '';
        }
        return implode(',', $this->headers[$normalized]);
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $this->validateHeader($name, $value);
        $new = clone $this;
        $normalized = $this->normalizeHeaderName($name);
        $new->headers[$normalized] = (array)$value;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->validateHeader($name, $value);
        $new = clone $this;
        $normalized = $this->normalizeHeaderName($name);
        if (!isset($new->headers[$normalized])) {
            $new->headers[$normalized] = [];
        }
        $new->headers[$normalized] = array_merge($new->headers[$normalized], (array)$value);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $normalized = $this->normalizeHeaderName($name);
        $new = clone $this;
        unset($new->headers[$normalized]);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * Normalize a header name.
     *
     * Converts the header name to lowercase to ensure uniformity.
     *
     * @param string $name The header name.
     *
     * @return string Normalized header name.
     */
    protected function normalizeHeaderName(string $name): string
    {
        return strtolower($name);
    }

    /**
     * Validate a header name and value.
     *
     * Ensures that the header name and value are valid according to RFC standards.
     *
     * @param string $name The header name.
     * @param string|string[] $value The header value or array of values.
     *
     * @throws \InvalidArgumentException If the header name or value is invalid.
     */
    protected function validateHeader(string $name, $value): void
    {
        // Validate header name (no forbidden characters)
        if (preg_match('/[^A-Za-z0-9\-]/', $name)) {
            throw new \InvalidArgumentException(sprintf('Invalid header name: "%s".', $name));
        }

        // Validate header values (must be strings, no CR/LF characters)
        $values = is_array($value) ? $value : [$value];
        foreach ($values as $val) {
            if (!is_string($val) || preg_match('/[\r\n]/', $val)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid header value for "%s". Cannot contain CR or LF characters.', $name),
                );
            }
        }
    }
}
