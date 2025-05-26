<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Exception;

use LapostaApi\Exception\ClientException;
use LapostaApi\Exception\LapostaException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

class ClientExceptionTest extends TestCase
{
    /**
     * Test if the constructor sets the correct properties
     */
    public function testConstructor(): void
    {
        // Mock of RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Test data
        $message = 'An error occurred while sending the request';
        $statusCode = 500;
        $responseHeaders = ['content-type' => ['application/json']];
        $responseBody = '{"error": "Internal Server Error"}';
        $code = 123;

        // Create a ClientException
        $exception = new ClientException(
            $message,
            $request,
            $statusCode,
            $responseHeaders,
            $responseBody,
            $code,
        );

        // Check if the exception implements the correct base interfaces/classes
        $this->assertInstanceOf(LapostaException::class, $exception);
        $this->assertInstanceOf(ClientExceptionInterface::class, $exception);

        // Check the properties
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($request, $exception->getRequest());
        $this->assertEquals($statusCode, $exception->getStatusCode());
        $this->assertEquals($responseHeaders, $exception->getResponseHeaders());
        $this->assertEquals($responseBody, $exception->getResponseBody());
    }

    /**
     * Test if getRequest returns the request correctly
     */
    public function testGetRequest(): void
    {
        // Mock of RequestInterface
        $request = $this->createMock(RequestInterface::class);

        // Create a ClientException with the request
        $exception = new ClientException(
            'Test exception',
            $request,
            200,
            [],
            '',
        );

        // Check if getRequest returns the request
        $this->assertSame($request, $exception->getRequest());
    }

    /**
     * Test if getRequest returns null when no request is provided
     */
    public function testGetRequestReturnsNullWhenNoRequestProvided(): void
    {
        // Create a ClientException without request
        $exception = new ClientException(
            'Test exception',
            null,
            200,
            [],
            '',
        );

        // Check if getRequest returns null
        $this->assertNull($exception->getRequest());
    }

    /**
     * Test if getResponseHeaders returns the headers correctly
     */
    public function testGetResponseHeaders(): void
    {
        // Test data
        $responseHeaders = [
            'content-type' => ['application/json'],
            'x-api-key' => ['abc123'],
        ];

        // Create a ClientException with the headers
        $exception = new ClientException(
            'Test exception',
            null,
            200,
            $responseHeaders,
            '',
        );

        // Check if getResponseHeaders returns the headers
        $this->assertEquals($responseHeaders, $exception->getResponseHeaders());
    }

    /**
     * Test if getResponseHeaders returns an empty array when no headers are provided
     */
    public function testGetResponseHeadersReturnsEmptyArrayWhenNoHeadersProvided(): void
    {
        // Create a ClientException with empty headers
        $exception = new ClientException(
            'Test exception',
            null,
            200,
            [],
            '',
        );

        // Check if getResponseHeaders returns an empty array
        $this->assertEquals([], $exception->getResponseHeaders());
    }

    /**
     * Test if getStatusCode returns the status code correctly
     */
    public function testGetStatusCode(): void
    {
        // Test data
        $statusCode = 404;

        // Create a ClientException with the status code
        $exception = new ClientException(
            'Test exception',
            null,
            $statusCode,
            [],
            '',
        );

        // Check if getStatusCode returns the status code
        $this->assertEquals($statusCode, $exception->getStatusCode());
    }

    /**
     * Test if getResponseBody returns the response body correctly
     */
    public function testGetResponseBody(): void
    {
        // Test data
        $responseBody = '{"error": "Not Found", "code": 404}';

        // Create a ClientException with the response body
        $exception = new ClientException(
            'Test exception',
            null,
            404,
            [],
            $responseBody,
        );

        // Check if getResponseBody returns the response body
        $this->assertEquals($responseBody, $exception->getResponseBody());
    }

    /**
     * Test if getResponseBody returns an empty string when no body is provided
     */
    public function testGetResponseBodyReturnsEmptyStringWhenNoBodyProvided(): void
    {
        // Create a ClientException with empty response body
        $exception = new ClientException(
            'Test exception',
            null,
            200,
            [],
            '',
        );

        // Check if getResponseBody returns an empty string
        $this->assertEquals('', $exception->getResponseBody());
    }

    /**
     * Test the inheritance from LapostaException
     */
    public function testInheritanceFromLapostaException(): void
    {
        // Test exception
        $exception = new ClientException(
            'Test exception',
            null,
            404,
            [],
            '',
            123,
        );

        // Check if the parent class properties are set correctly
        $this->assertEquals('Test exception', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test with a previous exception
     */
    public function testWithPreviousException(): void
    {
        // Create a previous exception
        $previous = new \RuntimeException('Previous error');

        // Create a ClientException with previous exception
        $exception = new ClientException(
            'Test exception',
            null,
            500,
            [],
            '',
            0,
            $previous,
        );

        // Check if the previous exception is set correctly
        $this->assertSame($previous, $exception->getPrevious());
    }
}
