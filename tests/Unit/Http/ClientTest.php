<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Adapter\CurlAdapter;
use LapostaApi\Adapter\StreamAdapter;
use LapostaApi\Exception\ClientException;
use LapostaApi\Http\Client;
use LapostaApi\Http\Request;
use LapostaApi\Http\Stream;
use LapostaApi\Http\StreamFactory;
use LapostaApi\Http\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;

/**
 * Unit tests for the HTTP Client implementation
 *
 * These tests verify the behavior of the HTTP Client using a mocked CurlAdapter
 * and StreamFactory to avoid actual network requests and file operations during testing.
 */
final class ClientTest extends TestCase
{
    private Client $httpClient;
    private ReflectionClass $reflectionClass;
    private mixed $curlHandle;
    private MockObject $curlAdapter;
    private MockObject $streamFactory;

    /**
     * Set up the test environment before each test
     *
     * Creates a Client instance with mocked dependencies and a reflection class.
     * Also creates a real curl handle for testing purposes.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a real curl handle for testing
        $this->curlHandle = curl_init();

        // Create mocked dependencies
        $this->curlAdapter = $this->createMock(CurlAdapter::class);
        $this->streamFactory = $this->createMock(StreamFactory::class);

        // Create a Client with the mocked dependencies
        $this->httpClient = new Client($this->curlAdapter, $this->streamFactory);
        $this->reflectionClass = new ReflectionClass(Client::class);
    }

    /**
     * Clean up after each test
     *
     * Properly closes the curl handle if it exists
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Clean up curl handle
        if ($this->curlHandle) {
            curl_close($this->curlHandle);
            $this->curlHandle = null;
        }

        parent::tearDown();
    }

    /**
     * Helper method to access protected methods for testing
     *
     * @param string $methodName The name of the protected method to invoke
     * @param array $parameters Parameters to pass to the method
     *
     * @return mixed The return value of the invoked method
     */
    private function invokeMethod(string $methodName, array $parameters = [])
    {
        $method = $this->reflectionClass->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->httpClient, $parameters);
    }

    /**
     * Helper to create a StreamInterface with content
     *
     * @param string $content Content of the stream
     *
     * @return StreamInterface
     */
    private function createStreamWithContent(string $content): StreamInterface
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('__toString')->willReturn($content);
        $streamMock->method('write')->willReturn(strlen($content));

        // Correct the return type for rewind to be void
        $streamMock->method('rewind')
                   ->willReturnCallback(function () {
                       // void return
                   });

        return $streamMock;
    }

    // Tests for sendRequest and its overall workflow
    public function testSendRequestSuccess(): void
    {
        // Mock curl adapter to return our curl handle
        $this->curlAdapter->method('init')
                          ->willReturn($this->curlHandle);

        // Configure adapter to simulate successful request
        $this->curlAdapter->method('setopt')
                          ->willReturn(true);

        // Prepare mock response
        $headers = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n\r\n";
        $body = '{"url":"https://httpbin.org/get","origin":"123.45.67.89"}';
        $responseContent = $headers . $body;

        // Mock execution of request
        $this->curlAdapter->method('exec')
                          ->willReturn($responseContent);

        // Mock response metadata
        $headerSize = mb_strlen($headers, '8bit');
        $this->curlAdapter->method('getInfo')
                          ->willReturnCallback(function ($handle, $option = null) use ($headerSize) {
                            if ($option === CURLINFO_HTTP_CODE) {
                                return 200;
                            }
                            if ($option === CURLINFO_HEADER_SIZE) {
                                return $headerSize;
                            }
                              return null;
                          });

        // No curl errors
        $this->curlAdapter->method('getErrno')
                          ->willReturn(0);

        // Mock the StreamFactory to return a stream
        $streamMock = $this->createStreamWithContent($body);
        $this->streamFactory->method('createStream')
                            ->with($body)
                            ->willReturn($streamMock);

        // Set up the Stream adapter for the Request body
        $streamAdapterMock = $this->createMock(StreamAdapter::class);
        $streamAdapterMock->method('isResource')->willReturn(true);

        // Create a request to test
        $resource = fopen('php://temp', 'r+');
        $requestStreamMock = new Stream($resource, $streamAdapterMock);

        // Create a URI using our own implementation to avoid mock issues
        $uri = new Uri('https://httpbin.org/get');

        // Create a request manually to avoid Stream creation issues
        $request = new Request('GET', $uri, $requestStreamMock);

        // Send the request using our client
        $response = $this->httpClient->sendRequest($request);

        // Verify the response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));

        // Close the resource
        fclose($resource);
    }

    public function testSendRequestFailsWithCurlError(): void
    {
        // Mock curl adapter to return our curl handle
        $this->curlAdapter->method('init')
                          ->willReturn($this->curlHandle);

        // Configure adapter to simulate successful setup
        $this->curlAdapter->method('setopt')
                          ->willReturn(true);

        // Mock execution failure
        $this->curlAdapter->method('exec')
                          ->willReturn(false);

        // Mock curl error information
        $this->curlAdapter->method('getError')
                          ->willReturn('Connection timed out');
        $this->curlAdapter->method('getErrno')
                          ->willReturn(28);

        // Set up the Stream adapter for the Request body
        $streamAdapterMock = $this->createMock(StreamAdapter::class);
        $streamAdapterMock->method('isResource')->willReturn(true);

        // Create a request to test
        $resource = fopen('php://temp', 'r+');
        $requestStreamMock = new Stream($resource, $streamAdapterMock);

        // Create a URI using our own implementation to avoid mock issues
        $uri = new Uri('https://httpbin.org/get');

        // Create a request manually to avoid Stream creation issues
        $request = new Request('GET', $uri, $requestStreamMock);

        // Expect exception during request sending
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('cURL execution failed: [28] Connection timed out');

        // Send the request, should throw exception
        try {
            $this->httpClient->sendRequest($request);
        } finally {
            // Close the resource to prevent leaks
            fclose($resource);
        }
    }

    // Tests for initializeCurl
    public function testInitializeCurlThrowsExceptionWhenCurlInitFails(): void
    {
        // Mock curl adapter om false terug te geven bij init
        $this->curlAdapter->method('init')
                          ->willReturn(false);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('https://example.com'));

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Could not initialize cURL session');

        $this->invokeMethod('initializeCurl', [$request]);
    }

    public function testInitializeCurlSetsTimeoutOptions(): void
    {
        // Stel beide timeouts in op de client
        $this->httpClient->setConnectionTimeout(15);
        $this->httpClient->setRequestTimeout(30);

        // Mock curl adapter om de curl handle terug te geven
        $this->curlAdapter->method('init')
                          ->willReturn($this->curlHandle);

        // Houd bij welke CURLOPT's zijn ingesteld
        $capturedOptions = [];

        // Gebruik een callback om alle CURLOPT instellingen vast te leggen
        $this->curlAdapter->method('setopt')
                          ->willReturnCallback(function ($ch, $option, $value) use (&$capturedOptions) {
                              $capturedOptions[$option] = $value;
                              return true;
                          });

        // Creëer een mock voor de request
        $request = $this->createMock(RequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('https://example.com'));
        $request->method('getMethod')->willReturn('GET');
        $request->method('getHeaders')->willReturn([]);
        $request->method('getBody')->willReturn($this->createMock(StreamInterface::class));

        // Roep de te testen methode aan
        $this->invokeMethod('initializeCurl', [$request]);

        // Verifieer dat de juiste timeout-opties zijn ingesteld
        $this->assertArrayHasKey(CURLOPT_CONNECTTIMEOUT, $capturedOptions);
        $this->assertEquals(15, $capturedOptions[CURLOPT_CONNECTTIMEOUT]);

        $this->assertArrayHasKey(CURLOPT_TIMEOUT, $capturedOptions);
        $this->assertEquals(30, $capturedOptions[CURLOPT_TIMEOUT]);
    }

    // Tests for setCurlMethod
    public function testSetCurlMethodForPostRequest(): void
    {
        // Mock curl adapter
        $this->curlAdapter->method('init')
                          ->willReturn($this->curlHandle);

        // Controleer of CURLOPT_POST wordt ingesteld voor POST requests
        $this->curlAdapter->expects($this->once())
                          ->method('setopt')
                          ->with(
                              $this->equalTo($this->curlHandle),
                              $this->equalTo(CURLOPT_POST),
                              $this->equalTo(true),
                          )
                          ->willReturn(true);

        $request = $this->createMock(RequestInterface::class);

        $this->invokeMethod('setCurlMethod', [$this->curlHandle, 'POST', $request]);
    }

    public function testSetCurlMethodForCustomRequest(): void
    {
        // Mock curl adapter
        $this->curlAdapter->method('init')
                          ->willReturn($this->curlHandle);

        // Controleer of CURLOPT_CUSTOMREQUEST wordt ingesteld voor niet-GET/POST requests
        $this->curlAdapter->expects($this->once())
                          ->method('setopt')
                          ->with(
                              $this->equalTo($this->curlHandle),
                              $this->equalTo(CURLOPT_CUSTOMREQUEST),
                              $this->equalTo('DELETE'),
                          )
                          ->willReturn(true);

        $request = $this->createMock(RequestInterface::class);

        $this->invokeMethod('setCurlMethod', [$this->curlHandle, 'DELETE', $request]);
    }

    // Tests for setCurlHeaders (and indirectly setCurlOption failure)
    public function testSetCurlHeadersThrowsOnFailure(): void
    {
        $this->curlAdapter->method('init')->willReturn($this->curlHandle);
        $this->curlAdapter->method('setopt')->willReturnCallback(
            fn($ch, $option, $value) => $option !== CURLOPT_HTTPHEADER,
        );
        $this->curlAdapter->expects($this->once())->method('close');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('https://httpbin.org/get'));
        $request->method('getMethod')->willReturn('GET');
        $request->method('getHeaders')->willReturn(['X-Fail' => ['true']]);
        $request->method('getBody')->willReturn($this->createMock(StreamInterface::class));

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Could not set cURL option ' . CURLOPT_HTTPHEADER);

        $this->httpClient->sendRequest($request);
    }

    // Tests for setCurlBody
    public function testSetCurlBodyWithNonEmptyBody(): void
    {
        // Mock curl adapter
        $this->curlAdapter->method('init')
                          ->willReturn($this->curlHandle);

        // Bij een niet-lege body moet CURLOPT_POSTFIELDS worden ingesteld
        $this->curlAdapter->expects($this->once())
                          ->method('setopt')
                          ->with(
                              $this->equalTo($this->curlHandle),
                              $this->equalTo(CURLOPT_POSTFIELDS),
                              $this->equalTo('test body'),
                          )
                          ->willReturn(true);

        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(9); // "test body" has 9 bytes
        $body->method('__toString')->willReturn('test body');

        $request = $this->createMock(RequestInterface::class);

        $this->invokeMethod('setCurlBody', [$this->curlHandle, $body, $request]);
    }

    public function testSetCurlBodyWithEmptyBody(): void
    {
        // Mock curl adapter
        $this->curlAdapter->method('init')
                          ->willReturn($this->curlHandle);

        // Bij een lege body mag CURLOPT_POSTFIELDS niet worden opgeroepen
        $this->curlAdapter->expects($this->never())
                          ->method('setopt')
                          ->with(
                              $this->anything(),
                              $this->equalTo(CURLOPT_POSTFIELDS),
                              $this->anything(),
                          );

        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(0);

        $request = $this->createMock(RequestInterface::class);

        $this->invokeMethod('setCurlBody', [$this->curlHandle, $body, $request]);
    }

    // Tests for splitResponseHeadersAndBody
    public function testSplitResponseHeadersAndBody(): void
    {
        // Example response with headers and body
        $headers = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n\r\n";
        $body = "{\"data\":\"test\"}";
        $responseContent = $headers . $body;

        // Calculate the byte length of the headers
        $headerSize = mb_strlen($headers, '8bit'); // This gives the byte count, equivalent to strlen() for ASCII

        [$rawHeaders, $responseBody] = $this->invokeMethod(
            'splitResponseHeadersAndBody',
            [$responseContent, $headerSize]
        );

        // Verify headers and body are correctly split
        $this->assertEquals($headers, $rawHeaders);
        $this->assertEquals($body, $responseBody);
    }

    public function testSplitResponseWithMultibyteCharacters(): void
    {
        // Headers with multibyte characters (e.g., UTF-8)
        $headers = "HTTP/1.1 200 OK\r\nContent-Type: application/json; charset=utf-8\r\nX-Custom: éèà日本語\r\n\r\n";
        $body = "{\"data\":\"测试\"}"; // Chinese characters in the body
        $responseContent = $headers . $body;

        // For ASCII characters, strlen() equals the number of bytes
        // But for multibyte characters, we must use mb_strlen with '8bit' encoding
        $headerSize = mb_strlen($headers, '8bit');

        [$rawHeaders, $responseBody] = $this->invokeMethod(
            'splitResponseHeadersAndBody',
            [$responseContent, $headerSize]
        );

        $this->assertEquals($headers, $rawHeaders);
        $this->assertEquals($body, $responseBody);
    }

    public function testSplitResponseWithEmptyContent(): void
    {
        [$rawHeaders, $body] = $this->invokeMethod('splitResponseHeadersAndBody', ['', 0]);

        $this->assertEquals('', $rawHeaders);
        $this->assertEquals('', $body);
    }

    // Tests for createResponse
    public function testCreateResponse(): void
    {
        $statusCode = 200;
        $rawHeaders = "Content-Type: application/json\r\nX-Test: value";
        $body = '{"success":true}';
        $request = $this->createMock(RequestInterface::class);

        // Configure the StreamFactory mock to return a stream
        $streamMock = $this->createStreamWithContent($body);
        $this->streamFactory->expects($this->once())
                            ->method('createStream')
                            ->with($body)
                            ->willReturn($streamMock);

        $response = $this->invokeMethod('createResponse', [$statusCode, $rawHeaders, $body, $request]);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertEquals(['value'], $response->getHeader('x-test'));
        $this->assertEquals('{"success":true}', (string)$response->getBody());
    }

    public function testCreateResponseFailsWhenStreamFactoryThrowsException(): void
    {
        $statusCode = 200;
        $rawHeaders = "Content-Type: application/json\r\n";
        $body = '{"data":"test"}';

        // Create a mock request for exception context
        $request = $this->createMock(RequestInterface::class);

        // Mock RuntimeException from StreamFactory
        $runtimeException = new \RuntimeException('Failed to create stream');

        // Configure the StreamFactory mock to throw an exception
        $this->streamFactory->expects($this->once())
                            ->method('createStream')
                            ->with($body)
                            ->willThrowException($runtimeException);

        // Access the protected method directly to set up the test properly
        $method = $this->reflectionClass->getMethod('createResponse');
        $method->setAccessible(true);

        // We expect a ClientException with the correct error message
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Could not create response stream: Failed to create stream');

        try {
            $method->invokeArgs($this->httpClient, [$statusCode, $rawHeaders, $body, $request]);
        } catch (ClientException $e) {
            // Check that the exception has the right message before re-throwing
            $this->assertEquals(
                'Could not create response stream: Failed to create stream',
                $e->getMessage(),
            );
            throw $e;
        }
    }

    // Tests for parseHeaders
    public function testParseHeadersWithValidInput(): void
    {
        $rawHeaders = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nX-API-Key: abc123\r\n" .
                      "Cache-Control: no-cache\r\nCache-Control: no-store\r\n";

        $expectedHeaders = [
            'content-type' => ['application/json'],
            'x-api-key' => ['abc123'],
            'cache-control' => ['no-cache', 'no-store'],
        ];

        $parsedHeaders = $this->invokeMethod('parseHeaders', [$rawHeaders]);
        $this->assertEquals($expectedHeaders, $parsedHeaders);
    }

    public function testParseEmptyHeaders(): void
    {
        $parsedHeaders = $this->invokeMethod('parseHeaders', ['']);
        $this->assertEquals([], $parsedHeaders);
    }

    public function testParseInvalidHeaders(): void
    {
        $invalidHeaders = "This is not a valid header\r\nAnother invalid line";
        $parsedHeaders = $this->invokeMethod('parseHeaders', [$invalidHeaders]);
        $this->assertEquals([], $parsedHeaders);
    }

    // Tests for timeout getters/setters
    public function testSetGetRequestTimeout(): void
    {
        // Default value should be null
        $this->assertNull($this->httpClient->getRequestTimeout());

        // Set a new value
        $this->httpClient->setRequestTimeout(30);
        $this->assertEquals(30, $this->httpClient->getRequestTimeout());

        // Reset to null
        $this->httpClient->setRequestTimeout(null);
        $this->assertNull($this->httpClient->getRequestTimeout());
    }

    public function testSetGetConnectionTimeout(): void
    {
        // Default value should be null
        $this->assertNull($this->httpClient->getConnectionTimeout());

        // Set a new value
        $this->httpClient->setConnectionTimeout(10);
        $this->assertEquals(10, $this->httpClient->getConnectionTimeout());

        // Reset to null
        $this->httpClient->setConnectionTimeout(null);
        $this->assertNull($this->httpClient->getConnectionTimeout());
    }
}
