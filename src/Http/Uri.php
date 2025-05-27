<?php

namespace LapostaApi\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected string $scheme = '';
    protected string $userInfo = '';
    protected string $host = '';
    protected ?int $port = null;
    protected string $path = '';
    protected string $query = '';
    protected string $fragment = '';

    /**
     * Constructs a new Uri instance by parsing a URI string.
     *
     * Parses the URI string into its components using parse_url() and sets the corresponding
     * instance properties. An empty URI string will result in default empty values.
     *
     * @param string $uri The URI string to parse.
     */
    public function __construct(string $uri = '')
    {
        $parts = parse_url($uri);

        $this->scheme = $parts['scheme'] ?? '';
        $this->host = $parts['host'] ?? '';
        $this->port = $parts['port'] ?? null;
        $this->path = $parts['path'] ?? '';
        $this->query = $parts['query'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';
        $this->userInfo = $parts['user'] ?? '';
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority(): string
    {
        $authority = $this->host;
        if (!empty($this->userInfo)) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritDoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme(string $scheme): UriInterface
    {
        $clone = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $clone = clone $this;
        $clone->userInfo = $password !== null ? $user . ':' . $password : $user;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withHost(string $host): UriInterface
    {
        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withPort(?int $port): UriInterface
    {
        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath(string $path): UriInterface
    {
        $clone = clone $this;
        $clone->path = $path;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery(string $query): UriInterface
    {
        $clone = clone $this;
        $clone->query = $query;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment(string $fragment): UriInterface
    {
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        if ($this->host !== '') {
            $uri .= '//';

            if ($this->userInfo !== '') {
                $uri .= $this->userInfo . '@';
            }

            $uri .= $this->host;

            if ($this->port !== null) {
                $uri .= ':' . $this->port;
            }
        }

        $uri .= $this->path;

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
