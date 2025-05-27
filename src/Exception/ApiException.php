<?php

declare(strict_types=1);

namespace LapostaApi\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ApiException extends LapostaException
{
    protected ?string $requestBody = null;
    protected ?string $responseBody = null;
    protected ?array $responseJson = null;

    /**
     * @param string $message The error message
     * @param ResponseInterface $response The full response object
     * @param int $code The exception code
     * @param Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message,
        protected RequestInterface $request,
        protected ResponseInterface $response,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the full request object
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the raw HTTP body (lazy loaded)
     */
    public function getRequestBody(): string
    {
        if ($this->requestBody === null) {
            $this->requestBody = (string)$this->request->getBody();
        }

        return $this->requestBody;
    }

    /**
     * Returns the full response object
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns the HTTP status code
     */
    public function getHttpStatus(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Returns the raw HTTP body (lazy loaded)
     */
    public function getResponseBody(): string
    {
        if ($this->responseBody === null) {
            $this->responseBody = (string)$this->response->getBody();
        }

        return $this->responseBody;
    }

    /**
     * Returns the parsed JSON response (lazy loaded)
     */
    public function getResponseData(): array
    {
        if ($this->responseJson === null) {
            $body = $this->getResponseBody();
            if (!empty($body)) {
                try {
                    $this->responseJson = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $this->responseJson = [];
                }
            } else {
                $this->responseJson = [];
            }
        }

        return $this->responseJson;
    }

    /**
     * Returns the error type if available
     */
    public function getErrorType(): ?string
    {
        return $this->getResponseData()['error']['type'] ?? null;
    }

    /**
     * Returns the error code if available
     */
    public function getErrorCode(): ?int
    {
        $code = $this->getResponseData()['error']['code'] ?? null;
        return $code !== null ? (int)$code : null;
    }

    /**
     * Returns the error parameter if available
     */
    public function getErrorParameter(): ?string
    {
        return $this->getResponseData()['error']['parameter'] ?? null;
    }

    /**
     * Returns the error message if available
     */
    public function getErrorMessage(): ?string
    {
        return $this->getResponseData()['error']['message'] ?? null;
    }


    /**
     * Returns a readable representation of the exception
     */
    public function __toString(): string
    {
        $str = parent::__toString();
        $str .= "\nHTTP Status: " . $this->getHttpStatus();

        $errorCode = $this->getErrorCode();
        if ($errorCode !== null) {
            $str .= "\nError Code: " . $errorCode;
        }

        $errorType = $this->getErrorType();
        if ($errorType !== null) {
            $str .= "\nError Type: " . $errorType;
        }

        $errorParameter = $this->getErrorParameter();
        if ($errorParameter !== null) {
            $str .= "\nError Parameter: " . $errorParameter;
        }

        $errorMessage = $this->getErrorMessage();
        if ($errorMessage !== null) {
            $str .= "\nError Message: " . $errorMessage;
        }

        $str .= "\nResponse Body: " . $this->getResponseBody();

        return $str;
    }
}
