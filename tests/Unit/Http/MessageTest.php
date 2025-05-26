<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Http\Message;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class MessageTest extends TestCase
{
    private Message $message;

    protected function setUp(): void
    {
        $this->message = $this->getMockBuilder(Message::class)
                              ->onlyMethods([])
                              ->getMock();
    }

    public function testGetAndSetProtocolVersion(): void
    {
        // Test default protocol version
        $this->assertSame('1.1', $this->message->getProtocolVersion());

        // Test setting a new protocol version
        $newMessage = $this->message->withProtocolVersion('2.0');
        $this->assertNotSame($this->message, $newMessage);
        $this->assertSame('2.0', $newMessage->getProtocolVersion());
    }

    public function testGetHeaders(): void
    {
        $this->assertSame([], $this->message->getHeaders());
    }

    public function testHasHeader(): void
    {
        $message = $this->message->withHeader('Content-Type', 'text/html');
        $this->assertTrue($message->hasHeader('Content-Type'));
        $this->assertFalse($this->message->hasHeader('Content-Type')); // Original is unmodified
    }

    public function testGetHeader(): void
    {
        $message = $this->message->withHeader('Content-Type', 'text/html');
        $this->assertSame(['text/html'], $message->getHeader('Content-Type'));
        $this->assertSame([], $this->message->getHeader('Non-Existent'));
    }

    public function testGetHeaderLine(): void
    {
        $message = $this->message->withHeader('Content-Type', ['text/html', 'charset=utf-8']);
        $this->assertSame('text/html,charset=utf-8', $message->getHeaderLine('Content-Type'));
        $this->assertSame('', $message->getHeaderLine('Non-Existent'));
    }

    public function testWithHeader(): void
    {
        $message = $this->message->withHeader('Content-Type', 'application/json');
        $this->assertSame(['application/json'], $message->getHeader('Content-Type'));
    }

    public function testWithAddedHeader(): void
    {
        $message = $this->message->withAddedHeader('X-Custom-Header', 'value1');
        $message = $message->withAddedHeader('X-Custom-Header', 'value2');

        $this->assertSame(['value1', 'value2'], $message->getHeader('X-Custom-Header'));
    }

    public function testWithoutHeader(): void
    {
        $message = $this->message->withHeader('Authorization', 'Bearer token');
        $this->assertTrue($message->hasHeader('Authorization'));

        $newMessage = $message->withoutHeader('Authorization');
        $this->assertFalse($newMessage->hasHeader('Authorization'));
    }

    public function testGetAndSetBody(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $newMessage = $this->message->withBody($streamMock);

        $this->assertNotSame($this->message, $newMessage);
        $this->assertSame($streamMock, $newMessage->getBody());
    }

    public function testValidateHeaderThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header name: "Invalid Header!".');

        $this->message->withHeader('Invalid Header!', 'value');
    }

    public function testValidateHeaderValueThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header value for "X-Test". Cannot contain CR or LF characters.');

        $this->message->withHeader('X-Test', "Invalid\nValue");
    }
}
