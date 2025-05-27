<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Http\StreamFactory;
use LapostaApi\Laposta;
use LapostaApi\Type\ContentType;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

abstract class BaseApi
{
    /**
     * Constructor method to initialize the Laposta instance.
     *
     * @param Laposta $laposta An instance of the Laposta class.
     */
    public function __construct(protected Laposta $laposta)
    {
    }

    /**
     * Connect to the API by building, sending, and handling the request.
     *
     * @param string $method The HTTP method (e.g., GET, POST).
     * @param array $pathSegments Additional URI path segments.
     * @param array $queryParams Query parameters.
     * @param ?array $body Request body data.
     * @param ContentType $contentType The content type for the request.
     *                                 Enum with possible values:
     *                                 ContentType::JSON,
     *                                 ContentType::FORM_URLENCODED
     *
     * @return array $data
     *
     * @throws \JsonException
     * @throws ApiException
     * @throws ClientException|ClientExceptionInterface
     */
    protected function sendRequest(
        string $method,
        array $pathSegments = [],
        array $queryParams = [],
        ?array $body = null,
        ContentType $contentType = ContentType::FORM,
    ): array {
        // Build the URI object with resource, path, and query parameters
        $uri = $this->buildUri($pathSegments, $queryParams);

        // Create the request
        $request = $this->createRequest($method, (string)$uri, $body, $contentType);

        // Send the request and receive the response
        $response = $this->dispatchRequest($request);

        // Process and return the response data
        return $this->handleResponse($request, $response);
    }

    /**
     * Build the URI object for the API call.
     *
     * @param array $pathSegments URI path segments.
     * @param array $queryParameters Query parameters.
     *
     * @return UriInterface The URI object representing the API endpoint.
     */
    protected function buildUri(array $pathSegments = [], array $queryParameters = []): UriInterface
    {
        // Base API URL from the Laposta instance
        $baseUrl = $this->laposta->getApiBaseUrl();
        $resource = $this->getResource();

        // Build the base URL with resource
        $url = rtrim($baseUrl, '/') . '/' . ltrim($resource, '/');

        // Add path segments
        foreach ($pathSegments as $segment) {
            if ($segment !== '' && $segment !== null) {
                $url .= '/' . urlencode((string)$segment);
            }
        }

        // Create a URI object
        $uri = $this->laposta->getUriFactory()->createUri($url);

        // Add query parameters if provided
        if (!empty($queryParameters)) {
            $uri = $uri->withQuery(http_build_query($queryParameters));
        }

        return $uri;
    }

    /**
     * Create the request with all required components.
     *
     * @param string $method The HTTP method.
     * @param string $url The full URL for the request.
     * @param array|null $body The request body data.
     * @param ContentType $contentType The content type for the request (determines how the body is formatted)
     *
     * @return RequestInterface The prepared request object.
     * @throws \JsonException
     */
    protected function createRequest(
        string $method,
        string $url,
        ?array $body = null,
        ContentType $contentType = ContentType::FORM,
    ): RequestInterface {
        // Create the request via the request factory
        $request = $this->laposta->getRequestFactory()->createRequest($method, $url);

        // Define default headers
        $defaultHeaders = [
            'Authorization' => 'Basic ' . base64_encode($this->laposta->getApiKey() . ':'),
            'User-Agent' => 'laposta-php-' . Laposta::VERSION,
            'X-Laposta-Client-User-Agent' => json_encode([
                'bindings_version' => Laposta::VERSION,
                'lang' => 'php',
                'lang_version' => phpversion(),
                'uname' => php_uname(),
            ]),
        ];

        // Add default headers
        foreach ($defaultHeaders as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        // If a body is provided, set it in the request
        if ($body !== null) {
            // Format body as JSON or form-urlencoded
            $formattedBody = $contentType->formatBody($body);

            // Create a stream and write the body to the stream
            $stream = $this->laposta->getStreamFactory()->createStream();
            $stream->write($formattedBody);
            $request = $request->withBody($stream);

            // Set the correct Content-Type header
            $request = $request->withHeader('Content-Type', $contentType->value);
        }

        return $request;
    }

    /**
     * Send the request and fetch the response.
     *
     * @param RequestInterface $request The prepared request object.
     *
     * @return ResponseInterface The response object.
     * @throws ClientException|ClientExceptionInterface
     */
    protected function dispatchRequest(RequestInterface $request): ResponseInterface
    {
        return $this->laposta->getHttpClient()->sendRequest($request);
    }

    /**
     * Handle the response and convert to array.
     *
     * @param RequestInterface $request The request to the API.
     * @param ResponseInterface $response The response from the API.
     *
     * @return array The decoded response data.
     *
     * @throws ApiException On HTTP errors.
     */
    protected function handleResponse(RequestInterface $request, ResponseInterface $response): array
    {
        // Check HTTP status code
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $body = (string)$response->getBody();
            $errorMessage = sprintf('API request failed with status code %d', $statusCode);

            // Try to extract error details from the response body
            try {
                $responseData = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                if (isset($responseData['error']) && is_array($responseData['error'])) {
                    // Add code, type, parameter and message if available
                    if (isset($responseData['error']['code'])) {
                        $errorMessage .= sprintf(', error.code: %s', $responseData['error']['code']);
                    }

                    if (isset($responseData['error']['type'])) {
                        $errorMessage .= sprintf(', error.type: %s', $responseData['error']['type']);
                    }

                    if (isset($responseData['error']['parameter'])) {
                        $errorMessage .= sprintf(', error.parameter: %s', $responseData['error']['parameter']);
                    }

                    if (isset($responseData['error']['message'])) {
                        $errorMessage .= sprintf(', error.message: %s', $responseData['error']['message']);
                    }
                }
            } catch (\JsonException $e) {
                // If the response body is not valid JSON, add the raw body
                if (!empty($body) && strlen($body) < 1000) {
                    $errorMessage .= sprintf(', response body: %s', $body);
                }
            }

            throw new ApiException(
                $errorMessage,
                $request,
                $response,
            );
        }

        // Decode JSON from the response body
        try {
            $body = (string)$response->getBody();
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ApiException(
                'Invalid JSON response from API',
                $request,
                $response,
                0,
                $e,
            );
        }
    }

    /**
     * Get the resource name for this API
     *
     * @return string The API resource name.
     */
    protected function getResource(): string
    {
        // Extract the resource name by removing namespace and suffix
        return strtolower(str_replace(['LapostaApi\\Api\\', 'Api'], '', static::class));
    }
}
