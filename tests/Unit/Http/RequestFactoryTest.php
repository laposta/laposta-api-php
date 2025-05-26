<?php

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Http\Request;
use LapostaApi\Http\RequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestFactoryTest extends TestCase
{
    private RequestFactory $requestFactory;

    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
    }

    public function testCreateRequest(): void
    {
        $method = 'POST';

        // Mocking the URI
        $uriMock = $this->createMock(UriInterface::class);

        $request = $this->requestFactory->createRequest($method, $uriMock);

        // Assert the type of the created request
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);

        // Assert the method and URI are set correctly
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uriMock, $request->getUri());
    }

    public function testStringUriIsConvertedToUriInstance(): void
    {
        $method = 'GET';
        $stringUri = 'https://example.com';

        $request = $this->requestFactory->createRequest($method, $stringUri);

        // Controleer of de URI correct is omgezet naar een Uri-instance
        $this->assertInstanceOf(UriInterface::class, $request->getUri());

        // Controleer of de URI dezelfde waarde heeft na omzetting
        $this->assertSame($stringUri, (string)$request->getUri());
    }

    public function testCreateRequestThrowsExceptionForInvalidUri(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The $uri argument must be a string or an instance of %s. %s given.',
                UriInterface::class,
                'int',
            ),
        );

        $method = 'GET';
        $invalidUri = 123;

        $this->requestFactory->createRequest($method, $invalidUri);
    }

    public function testHeadersAreAppliedToRequest(): void
    {
        $method = 'POST';

        // Mocking the URI
        $uriMock = $this->createMock(UriInterface::class);

        // Mock headers to apply
        $headers = [
            'Content-Type' => 'application/json',
            'Key' => 'Value',
        ];

        // Mock the factory to simulate header application, indien nodig
        $request = $this->requestFactory->createRequest($method, $uriMock);

        // Test adding headers
        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
            $this->assertTrue($request->hasHeader($headerName));
            $this->assertSame($headerValue, $request->getHeaderLine($headerName));
        }
    }
}
