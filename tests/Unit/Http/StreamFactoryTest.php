<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Adapter\StreamAdapter;
use LapostaApi\Http\Stream;
use LapostaApi\Http\StreamFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamFactoryTest extends TestCase
{
    /** @var StreamAdapter&MockObject */
    private StreamAdapter $streamAdapter;
    private StreamFactory $streamFactory;

    protected function setUp(): void
    {
        $this->streamAdapter = $this->createMock(StreamAdapter::class);
        $this->streamFactory = new StreamFactory($this->streamAdapter);
    }

    public function testCreateStreamWithNoContent(): void
    {
        // Mock resource handle
        $resource = fopen('php://memory', 'r+');

        // Mock de StreamAdapter gedragingen voor fopen
        $this->streamAdapter->expects($this->once())
                            ->method('fopen')
                            ->with('php://temp', 'r+')
                            ->willReturn($resource);

        // Mock validatie dat het een resource is
        $this->streamAdapter->expects($this->once())
                            ->method('isResource')
                            ->willReturn(true);

        // Creëer stream
        $stream = $this->streamFactory->createStream();

        // Valideer resultaat
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCreateStreamWithContent(): void
    {
        // Setup mock voor de resource en fopen
        $resource = fopen('php://memory', 'r+');
        $this->streamAdapter->expects($this->once())
                            ->method('fopen')
                            ->with('php://temp', 'r+')
                            ->willReturn($resource);

        // Mock de adapter methods
        $this->streamAdapter->method('isResource')
                            ->willReturn(true);

        // Verwacht dat fwrite wordt aangeroepen met de juiste content
        $this->streamAdapter->expects($this->once())
                            ->method('fwrite')
                            ->with($this->anything(), 'test content')
                            ->willReturn(strlen('test content'));

        // Verwacht dat rewind wordt aangeroepen
        $this->streamAdapter->expects($this->once())
                            ->method('rewind')
                            ->willReturn(true);

        // Creëer een stream met content
        $stream = $this->streamFactory->createStream('test content');

        // Valideer het resultaat
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCreateStreamThrowsExceptionOnFopenFailure(): void
    {
        // Mock adapter zodat fopen false retourneert
        $this->streamAdapter->expects($this->once())
                            ->method('fopen')
                            ->willReturn(false);

        // Verwacht een RuntimeException
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not create temporary stream');

        // Probeer een stream te maken, wat zou moeten falen
        $this->streamFactory->createStream();
    }

    public function testCreateStreamFromFileThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method createStreamFromFile() is not implemented');

        $this->streamFactory->createStreamFromFile('test.txt');
    }

    public function testCreateStreamFromResourceThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method createStreamFromResource() is not implemented');

        $resource = fopen('php://temp', 'r+');
        try {
            $this->streamFactory->createStreamFromResource($resource);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    public function testConstructorCreatesAdapterIfNotProvided(): void
    {
        // We moeten hier geen mocks gebruiken omdat we de echte adapter willen testen
        $factory = new StreamFactory();

        // Probeer een stream te maken
        $stream = $factory->createStream('test');

        // Valideer het resultaat
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    protected function tearDown(): void
    {
        unset($this->streamFactory, $this->streamAdapter);
        parent::tearDown();
    }
}
