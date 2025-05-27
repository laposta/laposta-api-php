<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\BaseApi;
use LapostaApi\Api\CampaignApi;
use LapostaApi\Exception\ApiException;
use LapostaApi\Http\Request;
use LapostaApi\Http\StreamFactory;
use LapostaApi\Http\Uri;
use LapostaApi\Laposta;
use LapostaApi\Type\ContentType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use ReflectionMethod;

final class BaseApiTest extends TestCase
{
    /** @var Laposta&MockObject */
    private Laposta $laposta;

    /** @var BaseApi&MockObject */
    private BaseApi $baseApi;

    /** @var ClientInterface&MockObject */
    private ClientInterface $httpClient;

    /** @var StreamFactoryInterface */
    private StreamFactoryInterface $streamFactory;

    /** @var StreamInterface */
    private StreamInterface $stream;

    /** @var RequestFactoryInterface&MockObject */
    private RequestFactoryInterface $requestFactory;

    /** @var UriFactoryInterface&MockObject */
    private UriFactoryInterface $uriFactory;

    protected function setUp(): void
    {
        // Mock Laposta and dependencies
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->streamFactory = new StreamFactory();
        $this->stream = $this->streamFactory->createStream();
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->uriFactory = $this->createMock(UriFactoryInterface::class);

        // Configure mock-implementatie
        $this->uriFactory->method('createUri')->willReturnCallback(function ($url) {
            return new Uri($url);
        });
        $this->laposta = $this->createMock(Laposta::class);
        $this->laposta->method('getApiKey')->willReturn('test_api_key');
        $this->laposta->method('getApiBaseUrl')->willReturn('https://api.laposta.nl/v2');
        $this->laposta->method('getHttpClient')->willReturn($this->httpClient);
        $this->laposta->method('getRequestFactory')->willReturn($this->requestFactory);
        $this->laposta->method('getUriFactory')->willReturn($this->uriFactory);
        $this->laposta->method('getStreamFactory')->willReturn($this->streamFactory);

        // Create concrete implementation of BaseApi for testing purposes
        $this->baseApi = $this->getMockBuilder(BaseApi::class)
            ->setConstructorArgs([$this->laposta])
            ->getMock();
    }

    public function testGetResource(): void
    {
        // Make protected method accessible for testing
        $method = new ReflectionMethod(BaseApi::class, 'getResource');
        $method->setAccessible(true);

        // Instantiate concrete subclass
        $memberApi = new CampaignApi($this->laposta);

        // Test scenario 1: Resource name equals class name
        $this->assertEquals('campaign', $method->invoke($memberApi));
    }

    public function testBuildUri(): void
    {
        // Get access to protected buildUri method
        $method = new ReflectionMethod(BaseApi::class, 'buildUri');
        $method->setAccessible(true);

        // Test scenario 1: Base URI without extra parameters
        $uri = $method->invoke($this->baseApi);
        $this->assertInstanceOf(UriInterface::class, $uri);

        // Test scenario 2: URI with path segments
        $uri = $method->invoke($this->baseApi, ['segment1', 'segment2']);
        $this->assertStringContainsString('/segment1/segment2', (string)$uri);

        // Test scenario 3: URI with query parameters
        $uri = $method->invoke($this->baseApi, [], ['param1' => 'value1', 'param2' => 'value2']);
        $this->assertStringContainsString('param1=value1&param2=value2', (string)$uri);

        // Test scenario 4: URI with both path segments and query parameters
        $uri = $method->invoke($this->baseApi, ['segment'], ['param' => 'value']);
        $this->assertStringContainsString('/segment', (string)$uri);
        $this->assertStringContainsString('param=value', (string)$uri);
    }

    public function testCreateGetRequest(): void
    {
        // Create an actual LapostaApi\Http\Request
        $originalRequest = new Request('GET', new Uri('https://api.laposta.nl/v2/resource'), $this->stream);

        // Set up what the requestFactory should return
        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://api.laposta.nl/v2/resource')
            ->willReturn($originalRequest);

        $method = new ReflectionMethod(BaseApi::class, 'createRequest');
        $method->setAccessible(true);

        // Call the method being tested
        $resultRequest = $method->invoke($this->baseApi, 'GET', 'https://api.laposta.nl/v2/resource');

        // Verify that the same instance is returned (no immutability check)
        $this->assertSame($originalRequest::class, $resultRequest::class);

        // Check if the correct headers were added to the request
        $expectedHeader = 'Authorization';
        $expectedValue = 'Basic ' . base64_encode('test_api_key:');

        $this->assertTrue(
            $resultRequest->hasHeader($expectedHeader),
            sprintf('Failed asserting that the "%s" header is present.', $expectedHeader),
        );

        $this->assertSame(
            [$expectedValue],
            $resultRequest->getHeader($expectedHeader),
            sprintf('Failed asserting that the "%s" header contains the correct value.', $expectedHeader),
        );
    }

    public function testCreatePostRequestWithJsonBody(): void
    {
        // Create an actual LapostaApi\Http\Request
        $originalRequest = new Request('POST', new Uri('https://api.laposta.nl/v2/resource'), $this->stream);

        // Set up what the requestFactory should return
        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'https://api.laposta.nl/v2/resource')
            ->willReturn($originalRequest);

        // Make the method accessible
        $method = new ReflectionMethod(BaseApi::class, 'createRequest');
        $method->setAccessible(true);

        // Execute the method with a JSON body
        $resultRequest = $method->invoke(
            $this->baseApi,
            'POST',
            'https://api.laposta.nl/v2/resource',
            ['key' => 'value'],      // Body
            ContentType::JSON,        // Content type: JSON
        );

        // Check if the correct headers were added to the request
        $this->assertTrue(
            $resultRequest->hasHeader('Authorization'),
            'The "Authorization" header is missing in the Request.',
        );

        $expectedAuth = 'Basic ' . base64_encode('test_api_key:');
        $this->assertSame(
            [$expectedAuth],
            $resultRequest->getHeader('Authorization'),
            'The value of the "Authorization" header is incorrect.',
        );

        // Check if the "Content-Type" header is set correctly
        $this->assertTrue(
            $resultRequest->hasHeader('Content-Type'),
            'The "Content-Type" header is missing in the Request.',
        );

        $this->assertSame(
            ['application/json'],
            $resultRequest->getHeader('Content-Type'),
            'The "Content-Type" header has the wrong type.',
        );

        // Check the body of the Request
        $bodyContent = (string)$resultRequest->getBody();
        $this->assertJson($bodyContent, 'The body must be valid JSON.');
        $this->assertSame(
            json_encode(['key' => 'value']),
            $bodyContent,
            'The JSON body is incorrect.',
        );
    }

    public function testCreatePostRequestWithFormBody(): void
    {
        // Create an actual LapostaApi\Http\Request
        $originalRequest = new Request('POST', new Uri('https://api.laposta.nl/v2/resource'), $this->stream);

        // Set up what the requestFactory should return
        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'https://api.laposta.nl/v2/resource')
            ->willReturn($originalRequest);

        // Make the method accessible
        $method = new ReflectionMethod(BaseApi::class, 'createRequest');
        $method->setAccessible(true);

        // Execute the method with a form-urlencoded body
        $resultRequest = $method->invoke(
            $this->baseApi,
            'POST',
            'https://api.laposta.nl/v2/resource',
            ['key' => 'value'],      // Body
            ContentType::FORM,        // Content type: form-urlencoded
        );

        // Check if the "Content-Type" header is set correctly
        $this->assertTrue(
            $resultRequest->hasHeader('Content-Type'),
            'The "Content-Type" header is missing in the Request.',
        );

        $this->assertSame(
            ['application/x-www-form-urlencoded'],
            $resultRequest->getHeader('Content-Type'),
            'The "Content-Type" header has the wrong type.',
        );

        // Check the content of the body
        $bodyContent = (string)$resultRequest->getBody();
        $this->assertSame(
            'key=value',
            $bodyContent,
            'The body is not correctly formatted as form-urlencoded data.',
        );
    }

    public function testDispatchRequest(): void
    {
        // Mock request and response
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        // Configure http client to return mock response
        $this->httpClient->method('sendRequest')->with($request)->willReturn($response);

        // Get access to protected dispatchRequest method
        $method = new ReflectionMethod(BaseApi::class, 'dispatchRequest');
        $method->setAccessible(true);

        // Test sending a request
        $result = $method->invoke($this->baseApi, $request);
        $this->assertSame($response, $result);
    }

    public function testHandleResponseSuccess(): void
    {
        // Mock response with successful status and JSON body
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('{"key":"value"}');

        // Mock request
        $request = $this->createMock(RequestInterface::class);

        // Mock Response
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        // Get access to protected handleResponse method
        $method = new ReflectionMethod(BaseApi::class, 'handleResponse');
        $method->setAccessible(true);

        // Test processing a successful response
        $result = $method->invoke($this->baseApi, $request, $response);
        $this->assertEquals(['key' => 'value'], $result);
    }

    public function testHandleResponseFailsWithHttpError(): void
    {
        // Mock request
        $request = $this->createMock(RequestInterface::class);

        // Mock response with error status
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        // Get access to protected handleResponse method
        $method = new ReflectionMethod(BaseApi::class, 'handleResponse');
        $method->setAccessible(true);

        // Test that ApiException is thrown on HTTP error
        $this->expectException(ApiException::class);
        $method->invoke($this->baseApi, $request, $response);
    }

    public function testHandleResponseWithInvalidJsonForHttpError(): void
    {
        // Mock request
        $request = $this->createMock(RequestInterface::class);

        // Create stream with invalid JSON
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('This is not valid JSON');

        // Mock response with error code (4xx) and invalid JSON body
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn($stream);

        // Get access to protected handleResponse method
        $method = new ReflectionMethod(BaseApi::class, 'handleResponse');
        $method->setAccessible(true);

        // Test that ApiException is thrown with correct error message
        try {
            $method->invoke($this->baseApi, $request, $response);
            $this->fail('An ApiException should be thrown');
        } catch (ApiException $e) {
            // Verify that error message contains raw body
            $this->assertStringContainsString('This is not valid JSON', $e->getMessage());
        }
    }

    public function testHandleResponseFailsWithInvalidJson(): void
    {
        // Mock response with successful status but invalid JSON
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('invalid json');

        // Mock request
        $request = $this->createMock(RequestInterface::class);

        // Mock Response
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        // Get access to protected handleResponse method
        $method = new ReflectionMethod(BaseApi::class, 'handleResponse');
        $method->setAccessible(true);

        // Test that ApiException is thrown on invalid JSON
        $this->expectException(ApiException::class);
        $method->invoke($this->baseApi, $request, $response);
    }

    public function testSendRequestCallsMethodsInCorrectOrder(): void
    {
        // Keep track of method call order
        $calledMethods = [];

        // Create mock object of BaseApi
        $baseApiMock = $this->getMockBuilder(BaseApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildUri', 'createRequest', 'dispatchRequest', 'handleResponse'])
            ->getMock();

        // Expectation for buildUri
        $baseApiMock->expects($this->once())
            ->method('buildUri')
            ->with(['segment'], ['param' => 'value'])
            ->willReturnCallback(function () use (&$calledMethods) {
                $calledMethods[] = 'buildUri';
                return $this->createMock(UriInterface::class);
            });

        // Expectation for createRequest
        $baseApiMock->expects($this->once())
            ->method('createRequest')
            ->with('GET', $this->isType('string'), null, $this->isInstanceOf(ContentType::class))
            ->willReturnCallback(function () use (&$calledMethods) {
                $calledMethods[] = 'createRequest';
                return $this->createMock(RequestInterface::class);
            });

        // Expectation for dispatchRequest
        $baseApiMock->expects($this->once())
            ->method('dispatchRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturnCallback(function () use (&$calledMethods) {
                $calledMethods[] = 'dispatchRequest';
                return $this->createMock(ResponseInterface::class);
            });

        // Expectation for handleResponse
        $baseApiMock->expects($this->once())
            ->method('handleResponse')
            ->with($this->isInstanceOf(RequestInterface::class), $this->isInstanceOf(ResponseInterface::class))
            ->willReturnCallback(function () use (&$calledMethods) {
                $calledMethods[] = 'handleResponse';
                return ['success' => true];
            });

        // Get access to sendRequest method
        $method = new ReflectionMethod(BaseApi::class, 'sendRequest');
        $method->setAccessible(true);

        // Call method
        $result = $method->invoke(
            $baseApiMock,
            'GET',
            ['segment'],
            ['param' => 'value'],
        );

        // Check that the order is correct
        $this->assertSame(
            ['buildUri', 'createRequest', 'dispatchRequest', 'handleResponse'],
            $calledMethods,
            'The method order is incorrect',
        );
    }

    protected function tearDown(): void
    {
        unset($this->laposta, $this->baseApi, $this->httpClient, $this->requestFactory);
        parent::tearDown();
    }
}
