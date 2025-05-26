<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Http\Request;
use LapostaApi\Http\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class RequestTest extends TestCase
{
    private Request $request;
    private UriInterface $uriMock;

    protected function setUp(): void
    {
        $this->uriMock = $this->createMock(UriInterface::class);
        $this->uriMock->method('getPath')->willReturn('/example');
        $this->uriMock->method('getQuery')->willReturn('');

        $this->request = new Request(
            'GET',
            $this->uriMock,
            $this->createMock(StreamInterface::class),
            ['Content-Type' => ['application/json']],
        );
    }

    public function testConstructor(): void
    {
        $this->assertSame('GET', $this->request->getMethod());
        $this->assertSame('/example', $this->request->getRequestTarget());
        $this->assertSame(['application/json'], $this->request->getHeader('Content-Type'));
    }


    public function testConstructorThrowsExceptionForInvalidMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP method provided: "INVALID".');

        new Request(
            'INVALID',
            $this->uriMock,
            $this->createMock(StreamInterface::class),
            ['Content-Type' => ['application/json']],
        );
    }

    public function testGetAndWithMethod(): void
    {
        $this->assertSame('GET', $this->request->getMethod());

        $newRequest = $this->request->withMethod('POST');
        $this->assertSame('POST', $newRequest->getMethod());
        $this->assertNotSame($this->request, $newRequest); // Ensure immutability

        $this->expectException(\InvalidArgumentException::class);
        $this->request->withMethod('Invalid Method');
    }

    public function testUpdateHostFromUriAddsHostHeader(): void
    {
        $uriWithHost = new Uri('https://example.com');

        $request = new Request(
            'GET',
            $uriWithHost,
            $this->createMock(StreamInterface::class),
        );

        $this->assertEquals(['example.com'], $request->getHeader('Host'));
    }

    public function testUpdateHostFromUriAddsHostHeaderWithPort(): void
    {
        $uriWithHostAndPort = new Uri('https://example.com:8080');

        $request = new Request(
            'GET',
            $uriWithHostAndPort,
            $this->createMock(StreamInterface::class),
        );

        $this->assertEquals(['example.com:8080'], $request->getHeader('Host'));
    }


    public function testGetUriAndWithUri(): void
    {
        $this->assertSame($this->uriMock, $this->request->getUri());

        $newUri = $this->createMock(UriInterface::class);
        $newUri->method('getPath')->willReturn('/new-path');
        $newRequest = $this->request->withUri($newUri);

        $this->assertSame($newUri, $newRequest->getUri());
        $this->assertNotSame($this->request, $newRequest); // Ensure immutability
    }

    public function testWithUriPreservesHost(): void
    {
        $uriWithHost = $this->createMock(UriInterface::class);
        $uriWithHost->method('getHost')->willReturn('example.com');
        $uriWithHost->method('getPath')->willReturn('/');

        $requestWithHost = $this->request->withUri($uriWithHost, true);
        $this->assertNotEmpty($requestWithHost->getHeaders());
        $this->assertNotSame($this->request, $requestWithHost); // Ensure immutability
    }

    public function testRequestTarget(): void
    {
        $this->assertSame('/example', $this->request->getRequestTarget());

        $newRequest = $this->request->withRequestTarget('/custom');
        $this->assertSame('/custom', $newRequest->getRequestTarget());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target; it must not contain whitespace.');
        $this->request->withRequestTarget("invalid target");
    }

    public function testHostHeaderUpdatedFromUri(): void
    {
        $this->uriMock->method('getHost')->willReturn('localhost');
        $requestWithHost = $this->request->withUri($this->uriMock);

        $this->assertEquals(['localhost'], $requestWithHost->getHeader('Host'));
    }

    public function testDefaultBody(): void
    {
        $newRequest = new Request(
            'POST',
            $this->uriMock,
            $this->createMock(StreamInterface::class),
        );

        $this->assertInstanceOf(StreamInterface::class, $newRequest->getBody());
    }
}
