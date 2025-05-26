<?php

declare(strict_types=1);

namespace LapostaApi\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

class ClientException extends LapostaException implements ClientExceptionInterface
{
    public function __construct(
        protected $message,
        protected ?RequestInterface $request,
        protected int $statusCode = 0,
        protected array $responseHeaders = [],
        protected string $responseBody = '',
        protected $code = 0,
        protected $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
