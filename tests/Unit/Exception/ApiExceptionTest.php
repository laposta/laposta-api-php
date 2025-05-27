<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Exception;

use LapostaApi\Exception\ApiException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ApiExceptionTest extends TestCase
{
    /**
     * Test if the constructor sets the correct properties
     */
    public function testConstructor(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        // Create ApiException
        $exception = new ApiException('Resource not found', $request, $response, 123);

        // Check properties
        $this->assertEquals('Resource not found', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
        $this->assertSame($response, $exception->getResponse());
    }

    /**
     * Test if getRequest returns the correct request object
     */
    public function testGetRequest(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);

        // Create ApiException
        $exception = new ApiException('Test exception', $request, $response);

        // Verify that the correct request object is returned
        $this->assertSame($request, $exception->getRequest());
    }

    /**
     * Test if getHttpStatus returns the correct status code
     */
    public function testGetHttpStatus(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(403);

        // Create ApiException
        $exception = new ApiException('Access denied', $request, $response);

        // Check if the correct status code is returned
        $this->assertEquals(403, $exception->getHttpStatus());
    }

    /**
     * Test if getRequestBody correctly retrieves and caches the body
     */
    public function testGetRequestBody(): void
    {
        // Mock StreamInterface
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('{"id": 123, "name": "Test"}');

        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);
        $request->method('getBody')->willReturn($stream);

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);

        // Create ApiException
        $exception = new ApiException('Test exception', $request, $response);

        // Verify getBody is only called once (caching works)
        $request->expects($this->once())->method('getBody');

        // Call getRequestBody and check result
        $this->assertEquals('{"id": 123, "name": "Test"}', $exception->getRequestBody());

        // Call again to test caching (body should only be requested once)
        $exception->getRequestBody();
    }

    /**
     * Test if getResponseBody correctly retrieves and caches the body
     */
    public function testGetResponseBody(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('{"error": "Access denied"}');

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Access denied', $request, $response);

        // Verify getBody is only called once (caching works)
        $response->expects($this->once())->method('getBody');

        // Call getResponseBody and check result
        $this->assertEquals('{"error": "Access denied"}', $exception->getResponseBody());

        // Call again to test caching (body should only be requested once)
        $exception->getResponseBody();
    }

    /**
     * Test if getJsonResponse correctly parses and caches valid JSON
     */
    public function testGetJsonResponseWithValidJson(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('{"error": "Not found", "code": 404}');

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Resource not found', $request, $response);

        // Call getJsonResponse and check result
        $expectedJson = ['error' => 'Not found', 'code' => 404];
        $this->assertEquals($expectedJson, $exception->getResponseData());
    }

    /**
     * Test if getJsonResponse returns empty array for invalid JSON
     */
    public function testGetJsonResponseWithInvalidJson(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('This is not valid JSON');

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Invalid response', $request, $response);

        // Call getJsonResponse and check result (should be empty array)
        $this->assertEquals([], $exception->getResponseData());
    }

    /**
     * Test if getJsonResponse returns empty array for empty body
     */
    public function testGetJsonResponseWithEmptyBody(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('');

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Empty response', $request, $response);

        // Call getJsonResponse and check result (should be empty array)
        $this->assertEquals([], $exception->getResponseData());
    }

    /**
     * Test error detail getters with complete error data
     */
    public function testErrorDetailGettersWithCompleteData(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface with complete error data
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn(json_encode([
            'error' => [
                'type' => 'invalid_input',
                'code' => 400,
                'parameter' => 'email',
                'message' => 'Email address is invalid'
            ]
        ]));

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Validation error', $request, $response);

        // Test each error detail getter
        $this->assertEquals('invalid_input', $exception->getErrorType());
        $this->assertEquals(400, $exception->getErrorCode());
        $this->assertEquals('email', $exception->getErrorParameter());
        $this->assertEquals('Email address is invalid', $exception->getErrorMessage());
    }

    /**
     * Test error detail getters with partial error data
     */
    public function testErrorDetailGettersWithPartialData(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface with partial error data (missing parameter and code)
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn(json_encode([
            'error' => [
                'type' => 'server_error',
                'message' => 'Internal server error occurred'
            ]
        ]));

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Server error', $request, $response);

        // Test each error detail getter
        $this->assertEquals('server_error', $exception->getErrorType());
        $this->assertNull($exception->getErrorCode()); // Should be null since it's missing
        $this->assertNull($exception->getErrorParameter()); // Should be null since it's missing
        $this->assertEquals('Internal server error occurred', $exception->getErrorMessage());
    }

    /**
     * Test error detail getters with no error data
     */
    public function testErrorDetailGettersWithNoErrorData(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface with response that has no error field
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn(json_encode([
            'status' => 'error',
            'message' => 'Something went wrong'
        ]));

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('General error', $request, $response);

        // Test each error detail getter should return null
        $this->assertNull($exception->getErrorType());
        $this->assertNull($exception->getErrorCode());
        $this->assertNull($exception->getErrorParameter());
        $this->assertNull($exception->getErrorMessage());
    }

    /**
     * Test if error details are included in __toString output
     */
    public function testToStringWithErrorDetails(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface with error details
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn(json_encode([
            'error' => [
                'type' => 'rate_limit_exceeded',
                'code' => 429,
                'parameter' => 'request',
                'message' => 'API rate limit exceeded'
            ]
        ]));

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(429);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Rate limit exceeded', $request, $response);

        // Convert to string and check content
        $string = (string)$exception;

        // String should contain all error details
        $this->assertStringContainsString('Error Code: 429', $string);
        $this->assertStringContainsString('Error Type: rate_limit_exceeded', $string);
        $this->assertStringContainsString('Error Parameter: request', $string);
        $this->assertStringContainsString('Error Message: API rate limit exceeded', $string);
    }

    /**
     * Test if __toString returns the correct string representation
     */
    public function testToString(): void
    {
        // Mock RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Mock StreamInterface
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('{"error": "Not found"}');

        // Mock ResponseInterface
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn($stream);

        // Create ApiException
        $exception = new ApiException('Resource not found', $request, $response, 123);

        // Convert to string and check content
        $string = (string)$exception;

        // String should contain exception class, message, status code and body
        $this->assertStringContainsString('ApiException', $string);
        $this->assertStringContainsString('Resource not found', $string);
        $this->assertStringContainsString('HTTP Status: 404', $string);
        $this->assertStringContainsString('Response Body: {"error": "Not found"}', $string);
    }
}
