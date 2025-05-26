<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use LapostaApi\Laposta;
use LapostaApi\Type\ContentType;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Base class for all API test cases
 *
 * Contains reusable methods and setup for all API tests
 */
abstract class BaseTestCase extends TestCase
{
    protected array $history;
    protected MockHandler $mockHandler;
    protected Laposta $laposta;

    protected function setUp(): void
    {
        $this->history = [];
        $this->mockHandler = new MockHandler();

        $stack = HandlerStack::create($this->mockHandler);
        $stack->push(Middleware::history($this->history));

        $httpClient = new GuzzleClient(['handler' => $stack]);
        $requestFactory = new HttpFactory();
        $this->laposta = new Laposta('test-api-key', $httpClient, $requestFactory);
    }

    /**
     * Helper method to execute and verify a test case with mock response
     *
     * @param callable $apiCall The API call to execute as a function
     * @param int $statusCode The HTTP status code for the mock response
     * @param array $responseData The data for the mock response
     * @param string $method The expected HTTP method
     * @param string $path The expected request path
     * @param array|null $expectedRequestData The expected request data (if applicable)
     * @param array|null $expectedResult The expected result values to verify
     * @param ContentType $contentType The expected content type
     * @param array|null $expectedQueryParams The expected query parameters
     *
     * @return array The result of the API call
     */
    protected function executeApiTest(
        callable $apiCall,
        int $statusCode,
        array $responseData,
        string $method,
        string $path,
        ?array $expectedRequestData = null,
        ?array $expectedResult = null,
        ContentType $contentType = ContentType::FORM,
        ?array $expectedQueryParams = null,
    ): array {
        // Prepare mock response
        $this->mockHandler->append(
            new Response($statusCode, [], json_encode($responseData)),
        );

        // Reset history for each test
        $this->history = [];

        // Execute the API call
        $result = $apiCall();

        // Verify the request
        $this->verifyRequest($method, $path, $expectedRequestData, $contentType, $expectedQueryParams);

        // Verify the result if expected values are provided
        if ($expectedResult !== null) {
            $this->verifyResult($expectedResult, $result);
        }

        return $result;
    }

    /**
     * Helper method for verifying API requests
     *
     * @param string $expectedMethod The expected HTTP method (GET, POST, etc.)
     * @param string $expectedPath The expected request path (without base path)
     * @param array|null $expectedRequestData The expected request data (for POST/PUT requests)
     * @param ContentType $contentType The expected content type
     * @param array|null $expectedQueryParams The expected query parameters (for GET/DELETE requests)
     *
     * @return RequestInterface The executed request for further verification if needed
     */
    protected function verifyRequest(
        string $expectedMethod,
        string $expectedPath,
        ?array $expectedRequestData = null,
        ContentType $contentType = ContentType::FORM,
        ?array $expectedQueryParams = null,
    ): RequestInterface {
        $this->assertCount(1, $this->history, 'Exactly one request should have been executed');

        /** @var RequestInterface $request */
        $request = $this->history[0]['request'];

        // Check HTTP method
        $this->assertEquals($expectedMethod, $request->getMethod());

        // Check the full path
        $expectedFullPath = $this->laposta->getApiBasePath() . $expectedPath;
        $this->assertEquals($expectedFullPath, $request->getUri()->getPath());

        // Check query parameters (if applicable)
        if ($expectedQueryParams !== null) {
            $queryString = $request->getUri()->getQuery();
            parse_str($queryString, $actualQueryParams);

            foreach ($expectedQueryParams as $key => $value) {
                $this->assertArrayHasKey($key, $actualQueryParams, "Query parameters missing expected key: $key");
                $this->assertEquals($value, $actualQueryParams[$key], "Query parameter value for $key does not match");
            }
        }

        // Check request data (if applicable)
        if ($expectedRequestData !== null && in_array($expectedMethod, ['POST', 'PUT', 'PATCH'])) {
            $this->assertTrue($request->hasHeader('Content-Type'));

            // Get the expected content type header value
            $expectedContentTypeHeader = $contentType->value;
            $this->assertEquals($expectedContentTypeHeader, $request->getHeaderLine('Content-Type'));

            $encodedBody = (string)$request->getBody();

            if ($contentType === ContentType::JSON) {
                // For JSON content type
                $decodedBody = json_decode($encodedBody, true);
                $this->assertJson($encodedBody, 'Request body should be valid JSON');

                // Compare the entire body at once for JSON
                $this->assertEquals($expectedRequestData, $decodedBody);
            } else {
                // For form-urlencoded content type
                parse_str($encodedBody, $decodedBody);

                foreach ($expectedRequestData as $key => $value) {
                    $this->assertArrayHasKey($key, $decodedBody, "Request data is missing expected key: $key");
                    $this->assertEquals($value, $decodedBody[$key], "Value for $key does not match");
                }
            }
        } elseif ($expectedMethod === 'GET' || $expectedMethod === 'DELETE') {
            // For GET and DELETE requests the body must be empty
            $this->assertEquals('', (string)$request->getBody());
        }

        return $request;
    }

    /**
     * Helper method for verifying API results
     *
     * @param array $expectedValues The expected values that should occur in the result
     * @param array $actualResult The actual result
     */
    protected function verifyResult(array $expectedValues, array $actualResult): void
    {
        foreach ($expectedValues as $key => $value) {
            $this->assertArrayHasKey($key, $actualResult, "Result is missing expected key: $key");
            $this->assertEquals($value, $actualResult[$key], "Value for $key does not match");
        }
    }
}
