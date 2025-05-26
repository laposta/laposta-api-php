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
     * Retrieve the HTTP protocol version.
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Return a new message with the specified protocol version.
     *
     * @param string $version
     *
     * @return MessageInterface
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * Retrieve all headers.
     *
     * @return array<string, array<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Check if a specific header exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        $normalized = $this->normalizeHeaderName($name);
        return isset($this->headers[$normalized]);
    }

    /**
     * Retrieve a specific header in array format.
     *
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader(string $name): array
    {
        $normalized = $this->normalizeHeaderName($name);
        return $this->headers[$normalized] ?? [];
    }

    /**
     * Retrieve a specific header as a single string (comma-separated).
     *
     * @param string $name
     *
     * @return string
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
     * Return a new message with the specified header (old values are overwritten).
     *
     * @param string $name
     * @param string|string[] $value
     *
     * @return MessageInterface
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $this->validateHeader($name, $value); // Validate the header name and value
        $new = clone $this;
        $normalized = $this->normalizeHeaderName($name); // Normalize the header name
        $new->headers[$normalized] = (array)$value; // Store as an array
        return $new;
    }

    /**
     * Return a new message with an added header (existing values are preserved).
     *
     * @param string $name
     * @param string|string[] $value
     *
     * @return MessageInterface
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->validateHeader($name, $value); // Validate the header name and value
        $new = clone $this;
        $normalized = $this->normalizeHeaderName($name); // Normalize the header name
        if (!isset($new->headers[$normalized])) {
            $new->headers[$normalized] = [];
        }
        $new->headers[$normalized] = array_merge($new->headers[$normalized], (array)$value);
        return $new;
    }

    /**
     * Return a new message with a removed header.
     *
     * @param string $name
     *
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $normalized = $this->normalizeHeaderName($name); // Normalize the header name
        $new = clone $this;
        unset($new->headers[$normalized]);
        return $new;
    }

    /**
     * Retrieve the body.
     *
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Return a new message with a new body object.
     *
     * @param StreamInterface $body
     *
     * @return MessageInterface
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
