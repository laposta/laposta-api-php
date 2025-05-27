<?php

declare(strict_types=1);

namespace LapostaApi\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface
{
    protected StreamInterface $body;
    protected array $headers = [];

    /**
     * Constructor for the Response class.
     *
     * @param int $statusCode HTTP status code.
     * @param string $reasonPhrase Optional reason phrase, defaults based on the status code.
     */
    public function __construct(
        protected int $statusCode = 200,
        protected string $reasonPhrase = '',
    ) {
        $this->reasonPhrase = $reasonPhrase ?: $this->getDefaultReasonPhrase($statusCode);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase !== '' ? $reasonPhrase : $new->getDefaultReasonPhrase($code);
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    protected function getDefaultReasonPhrase(int $code): string
    {
        return match ($code) {
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Request Failed',
            404 => 'Not Found',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            default => '',
        };
    }
}
