<?php

declare(strict_types=1);

namespace LapostaApi\Http;

use LapostaApi\Adapter\CurlAdapter;
use LapostaApi\Exception\ClientException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-18 compatible HTTP client implementation using cURL
 */
class Client implements ClientInterface
{
    protected ?int $requestTimeout = null;
    protected ?int $connectionTimeout = null;

    /**
     * Constructor to initialize the HTTP client
     *
     * @param CurlAdapter $curl Optional custom cURL adapter
     * @param StreamFactory $streamFactory Optional custom stream factory
     * @param ResponseFactory $responseFactory Optional custom response factory
     */
    public function __construct(
        protected CurlAdapter $curl = new CurlAdapter(),
        protected StreamFactory $streamFactory = new StreamFactory(),
        protected ResponseFactory $responseFactory = new ResponseFactory(),
    ) {
    }

    /**
     * Performs an HTTP request using a PSR-7 Request object
     *
     * @param RequestInterface $request The PSR-7 compatible request object
     *
     * @return ResponseInterface The PSR-7 compatible response object
     * @throws ClientException When a connection error occurs during the HTTP request
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $ch = $this->initializeCurl($request);
        return $this->executeRequestAndHandleResponse($ch, $request);
    }

    /**
     * Initialize a cURL session with the given request parameters
     *
     * @param RequestInterface $request The PSR-7 compatible request
     *
     * @return \CurlHandle The initialized cURL handle
     * @throws ClientException When cURL initialization fails
     */
    protected function initializeCurl(RequestInterface $request): \CurlHandle
    {
        $ch = $this->curl->init();

        if ($ch === false) {
            throw new ClientException('Could not initialize cURL session', $request);
        }

        $this->setCurlOption($ch, CURLOPT_URL, (string)$request->getUri(), $request);
        $this->setCurlOption($ch, CURLOPT_HEADER, true, $request);
        $this->setCurlOption($ch, CURLOPT_RETURNTRANSFER, true, $request);

        if ($this->connectionTimeout !== null) {
            $this->setCurlOption($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout, $request);
        }

        if ($this->requestTimeout !== null) {
            $this->setCurlOption($ch, CURLOPT_TIMEOUT, $this->requestTimeout, $request);
        }

        $this->setCurlMethod($ch, $request->getMethod(), $request);
        $this->setCurlHeaders($ch, $request->getHeaders(), $request);
        $this->setCurlBody($ch, $request->getBody(), $request);

        return $ch;
    }

    /**
     * Set a cURL option and check if it was successful
     *
     * @param \CurlHandle $ch The cURL handle
     * @param int $option The CURLOPT_* option to set
     * @param mixed $value The value to set the option to
     * @param RequestInterface $request The original request for exception context
     *
     * @throws ClientException When setting the option fails
     */
    protected function setCurlOption(\CurlHandle $ch, int $option, $value, RequestInterface $request): void
    {
        if (!$this->curl->setopt($ch, $option, $value)) {
            $this->curl->close($ch);
            throw new ClientException("Could not set cURL option {$option}", $request);
        }
    }

    /**
     * Configure the HTTP method for the cURL handle
     *
     * @param \CurlHandle $ch The cURL handle
     * @param string $method The HTTP method
     * @param RequestInterface $request The original request for exception context
     *
     * @throws ClientException When setting cURL options fails
     */
    protected function setCurlMethod(\CurlHandle $ch, string $method, RequestInterface $request): void
    {
        $method = strtoupper($method);
        if ($method === 'POST') {
            $this->setCurlOption($ch, CURLOPT_POST, true, $request);
        } elseif ($method !== 'GET') {
            $this->setCurlOption($ch, CURLOPT_CUSTOMREQUEST, $method, $request);
        }
    }

    /**
     * Configure the HTTP headers for the cURL handle
     *
     * @param \CurlHandle $ch The cURL handle
     * @param array $requestHeaders The HTTP headers from the request
     * @param RequestInterface $request The original request for exception context
     *
     * @throws ClientException When setting cURL options fails
     */
    protected function setCurlHeaders(\CurlHandle $ch, array $requestHeaders, RequestInterface $request): void
    {
        $headers = [];
        foreach ($requestHeaders as $name => $values) {
            $name = strtolower($name);
            foreach ($values as $value) {
                $headers[] = "{$name}: {$value}";
            }
        }
        $this->setCurlOption($ch, CURLOPT_HTTPHEADER, $headers, $request);
    }

    /**
     * Configure the request body for the cURL handle
     *
     * @param \CurlHandle $ch The cURL handle
     * @param StreamInterface $body The request body stream
     * @param RequestInterface $request The original request for exception context
     *
     * @throws ClientException When setting cURL options fails
     */
    protected function setCurlBody(\CurlHandle $ch, StreamInterface $body, RequestInterface $request): void
    {
        if ($body->getSize() > 0) {
            $this->setCurlOption($ch, CURLOPT_POSTFIELDS, (string)$body, $request);
        }
    }

    /**
     * Execute the cURL request and handle the response
     *
     * @param \CurlHandle $ch The cURL handle
     * @param RequestInterface $request The original request
     *
     * @return ResponseInterface The response object
     * @throws ClientException When a connection error occurs
     */
    protected function executeRequestAndHandleResponse(\CurlHandle $ch, RequestInterface $request): ResponseInterface
    {
        $responseContent = $this->curl->exec($ch);

        if ($responseContent === false) {
            $error = $this->curl->getError($ch);
            $errno = $this->curl->getErrno($ch);
            $this->curl->close($ch);

            throw new ClientException(
                "cURL execution failed: [{$errno}] {$error}",
                $request,
            );
        }

        $status = (int)$this->curl->getInfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int)$this->curl->getInfo($ch, CURLINFO_HEADER_SIZE);
        $this->curl->close($ch);

        [$rawHeaders, $body] = $this->splitResponseHeadersAndBody($responseContent, $headerSize);

        return $this->createResponse($status, $rawHeaders, $body, $request);
    }

    /**
     * Split the raw response into headers and body
     *
     * @param string $content Raw response content
     * @param int $headerSize Size of the header portion
     *
     * @return array Array containing [headers, body]
     */
    protected function splitResponseHeadersAndBody(string $content, int $headerSize): array
    {
        $rawHeaders = substr($content, 0, $headerSize);
        $body = substr($content, $headerSize);
        return [$rawHeaders, $body];
    }

    /**
     * Create a Response object from the raw response data
     *
     * @param int $status HTTP status code
     * @param string $rawHeaders Raw response headers
     * @param string $body Response body
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ClientException When response creation fails
     */
    protected function createResponse(
        int $status,
        string $rawHeaders,
        string $body,
        RequestInterface $request,
    ): ResponseInterface {
        // create stream
        try {
            $stream = $this->streamFactory->createStream($body);
        } catch (\RuntimeException $e) {
            throw new ClientException("Could not create response stream: " . $e->getMessage(), $request);
        }

        // create response
        $response = $this->responseFactory->createResponse($status);
        foreach ($this->parseHeaders($rawHeaders) as $name => $values) {
            $response = $response->withHeader($name, $values);
        }
        $response = $response->withBody($stream);

        return $response;
    }

    /**
     * Parse raw HTTP headers into an associative array
     *
     * @param string $raw Raw header string
     *
     * @return array Parsed headers
     */
    protected function parseHeaders(string $raw): array
    {
        $headers = [];
        foreach (explode("\r\n", trim($raw)) as $line) {
            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(':', $line, 2);
                $name = strtolower(trim($name));
                $headers[$name][] = trim($value);
            }
        }
        return $headers;
    }

    /**
     * Get the request timeout value
     */
    public function getRequestTimeout(): ?int
    {
        return $this->requestTimeout;
    }

    /**
     * Set the request timeout value
     */
    public function setRequestTimeout(?int $value): void
    {
        $this->requestTimeout = $value;
    }

    /**
     * Get the connection timeout value
     */
    public function getConnectionTimeout(): ?int
    {
        return $this->connectionTimeout;
    }

    /**
     * Set the connection timeout value
     */
    public function setConnectionTimeout(?int $value): void
    {
        $this->connectionTimeout = $value;
    }
}
