<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Adapter\StreamAdapter;
use LapostaApi\Http\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StreamTest extends TestCase
{
    private StreamAdapter|MockObject $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        // Maak een mock van de StreamAdapter die we kunnen configureren voor tests
        $this->adapter = $this->createMock(StreamAdapter::class);

        // Standaardgedrag voor de adapter-methodes
        $this->adapter->method('isResource')->willReturn(true);
        $this->adapter->method('streamGetMetaData')->willReturn([
            'mode' => 'r+',
            'seekable' => true,
            'uri' => 'php://temp',
        ]);
    }

    private function createReadOnlyStream(): Stream
    {
        $adapter = $this->createMock(StreamAdapter::class);
        $adapter->method('isResource')->willReturn(true);
        $adapter->method('streamGetMetaData')->willReturn(['mode' => 'r', 'seekable' => true, 'uri' => 'php://memory']);

        return new Stream(fopen('php://memory', 'r'), $adapter);
    }

    private function createWriteOnlyStream(): Stream
    {
        $adapter = $this->createMock(StreamAdapter::class);
        $adapter->method('isResource')->willReturn(true);
        $adapter->method('streamGetMetaData')->willReturn(['mode' => 'w', 'seekable' => true, 'uri' => 'php://temp']);

        return new Stream(fopen('php://temp', 'w'), $adapter);
    }

    private function createReadWriteStream(): Stream
    {
        $resource = fopen('php://temp', 'r+');
        return new Stream($resource, $this->adapter);
    }

    public function testConstructorRejectsNonResource(): void
    {
        // Maak een nieuwe adapter mock specifiek voor deze test
        $mockAdapter = $this->createMock(StreamAdapter::class);
        $mockAdapter->method('isResource')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream must be a valid resource.');

        // @phpstan-ignore-next-line
        new Stream('not a resource', $mockAdapter);
    }

    public function testCanInstantiateStream(): void
    {
        $stream = $this->createReadWriteStream();
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testWriteAndReadFromStream(): void
    {
        $this->adapter->method('fwrite')->willReturn(13);
        $this->adapter->method('fread')->willReturn('Hello, World!');
        $this->adapter->method('rewind')->willReturn(true);

        $stream = $this->createReadWriteStream();
        $stream->write('Hello, World!');

        $stream->rewind();
        $content = $stream->read(13);

        $this->assertEquals('Hello, World!', $content);
    }

    public function testToStringReturnsEmptyStringForNonReadableStream(): void
    {
        $adapter = $this->createMock(StreamAdapter::class);
        $adapter->method('isResource')->willReturn(true);
        $adapter->method('streamGetMetaData')->willReturn(['mode' => 'w', 'seekable' => true]);
        $adapter->method('fwrite')->willReturn(13);

        $resource = fopen('php://temp', 'w');
        $stream = new Stream($resource, $adapter);
        $stream->write('Hello, World!');

        $this->assertEquals('', (string)$stream);
    }

    public function testToStringReturnsEmptyStringForNonSeekableStream(): void
    {
        $adapter = $this->createMock(StreamAdapter::class);
        $adapter->method('isResource')->willReturn(true);
        $adapter->method('streamGetMetaData')->willReturn(['mode' => 'r+', 'seekable' => false]);

        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource, $adapter);

        $this->assertEquals('', (string)$stream);
    }

    public function testStreamToString(): void
    {
        $this->adapter->method('fwrite')->willReturn(11);
        $this->adapter->method('rewind')->willReturn(true);
        $this->adapter->method('streamGetContents')->willReturn('Sample text');

        $stream = $this->createReadWriteStream();
        $stream->write('Sample text');
        $stream->rewind();

        $this->assertEquals('Sample text', (string)$stream);
    }

    public function testStreamIsWritable(): void
    {
        // Test verschillende modes
        $modes = [
            'r' => false,
            'w' => true,
            'a' => true,
            'r+' => true,
            'w+' => true,
            'a+' => true,
        ];

        foreach ($modes as $mode => $expected) {
            $adapter = $this->createMock(StreamAdapter::class);
            $adapter->method('isResource')->willReturn(true);
            $adapter->method('streamGetMetaData')->willReturn(['mode' => $mode]);

            $resource = fopen('php://temp', 'r+');
            $stream = new Stream($resource, $adapter);

            $this->assertEquals(
                $expected,
                $stream->isWritable(),
                "Mode $mode zou " . ($expected ? "schrijfbaar" : "niet schrijfbaar") . " moeten zijn"
            );
        }
    }

    public function testStreamIsReadable(): void
    {
        // Test verschillende modes
        $modes = [
            'r' => true,
            'w' => false,
            'a' => false,
            'r+' => true,
            'w+' => true,
            'a+' => true,
        ];

        foreach ($modes as $mode => $expected) {
            $adapter = $this->createMock(StreamAdapter::class);
            $adapter->method('isResource')->willReturn(true);
            $adapter->method('streamGetMetaData')->willReturn(['mode' => $mode]);

            $resource = fopen('php://temp', 'r+');
            $stream = new Stream($resource, $adapter);

            $this->assertEquals(
                $expected,
                $stream->isReadable(),
                "Mode $mode zou " . ($expected ? "leesbaar" : "niet leesbaar") . " moeten zijn"
            );
        }
    }

    public function testStreamIsSeekable(): void
    {
        $this->adapter->method('streamGetMetaData')->willReturn(['seekable' => true]);
        $stream = $this->createReadWriteStream();
        $this->assertTrue($stream->isSeekable());

        $adapter = $this->createMock(StreamAdapter::class);
        $adapter->method('isResource')->willReturn(true);
        $adapter->method('streamGetMetaData')->willReturn(['seekable' => false]);

        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource, $adapter);
        $this->assertFalse($stream->isSeekable());
    }

    public function testStreamSize(): void
    {
        $this->adapter->method('fwrite')->willReturn(9);
        $this->adapter->method('fstat')->willReturn(['size' => 9]);

        $stream = $this->createReadWriteStream();
        $stream->write('Test size');

        $this->assertEquals(9, $stream->getSize());
    }

    public function testGetSizeReturnsNullWhenStreamIsDetached(): void
    {
        $stream = $this->createReadWriteStream();
        $stream->detach();

        $this->assertNull($stream->getSize());
    }

    public function testGetSizeReturnsZeroWhenFstatFails(): void
    {
        $this->adapter->method('fstat')->willReturn(['size' => 0]);

        $stream = $this->createReadWriteStream();
        $this->assertEquals(0, $stream->getSize());
    }

    public function testTellStreamPointerPosition(): void
    {
        $this->adapter->method('fwrite')->willReturn(12);
        $this->adapter->method('ftell')->willReturn(12);

        $stream = $this->createReadWriteStream();
        $stream->write('Pointer test');

        $this->assertEquals(12, $stream->tell());
    }

    public function testTellThrowsExceptionWhenStreamIsDetached(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No stream available.');

        $stream = $this->createReadWriteStream();
        $stream->detach();
        $stream->tell();
    }

    public function testTellThrowsExceptionWhenFtellFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine position of pointer.');

        $this->adapter->method('ftell')->willReturn(false);

        $stream = $this->createReadWriteStream();
        $stream->tell();
    }

    public function testRewindStream(): void
    {
        $s = 'Rewind test';
        $this->adapter->method('fwrite')->willReturn(strlen($s));
        $this->adapter->method('ftell')->willReturnOnConsecutiveCalls(strlen($s), 0);
        $this->adapter->method('rewind')->willReturn(true);

        $stream = $this->createReadWriteStream();
        $stream->write($s);

        $this->assertEquals(strlen($s), $stream->tell());
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    public function testRewindThrowsExceptionWhenRewindFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to rewind the stream.');

        $this->adapter->method('rewind')->willReturn(false);

        $stream = $this->createReadWriteStream();
        $stream->rewind();
    }

    public function testSeekStream(): void
    {
        $this->adapter->method('fwrite')->willReturn(9);
        $this->adapter->method('fseek')->willReturn(0);
        $this->adapter->method('ftell')->willReturn(5);

        $stream = $this->createReadWriteStream();
        $stream->write('Seek test');
        $stream->seek(5);

        $this->assertEquals(5, $stream->tell());
    }

    public function testSeekThrowsExceptionWhenNotSeekable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');

        $adapter = $this->createMock(StreamAdapter::class);
        $adapter->method('isResource')->willReturn(true);
        $adapter->method('streamGetMetaData')->willReturn(['seekable' => false]);

        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource, $adapter);
        $stream->seek(5);
    }

    public function testSeekThrowsExceptionWhenFseekFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');

        $this->adapter->method('fseek')->willReturn(-1);

        $stream = $this->createReadWriteStream();
        $stream->seek(5);
    }

    public function testCloseStream(): void
    {
        $this->adapter->method('fclose')->willReturn(true);

        $stream = $this->createReadWriteStream();
        $stream->close();

        $this->assertFalse($stream->isReadable());
    }

    public function testDetachStream(): void
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource, $this->adapter);
        $detachedResource = $stream->detach();

        $this->assertIsResource($detachedResource);
        $this->assertNull($stream->getSize());
    }

    public function testWriteAfterDetachThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable.');

        $stream = $this->createReadWriteStream();
        $stream->detach();
        $stream->write('This should fail');
    }

    public function testWriteThrowsExceptionWhenStreamIsNotWritable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable.');

        $stream = $this->createReadOnlyStream();
        $stream->write('This should fail');
    }

    public function testWriteThrowsExceptionWhenFwriteFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to write to the stream.');

        $this->adapter->method('fwrite')->willReturn(false);

        $stream = $this->createReadWriteStream();
        $stream->write('This should fail');
    }

    public function testReadThrowsExceptionWhenStreamIsNotReadable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable.');

        $stream = $this->createWriteOnlyStream();
        $stream->read(5);
    }

    public function testReadThrowsExceptionWhenFreadFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read from the stream.');

        $this->adapter->method('fread')->willReturn(false);

        $stream = $this->createReadWriteStream();
        $stream->read(5);
    }

    public function testGetContents(): void
    {
        $this->adapter->method('fwrite')->willReturn(23);
        $this->adapter->method('rewind')->willReturn(true);
        $this->adapter->method('streamGetContents')->willReturn('Testing content retrieval');

        $stream = $this->createReadWriteStream();
        $stream->write('Testing content retrieval');
        $stream->rewind();

        $this->assertEquals('Testing content retrieval', $stream->getContents());
    }

    public function testGetContentsThrowsExceptionWhenStreamIsNotReadable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable.');

        $stream = $this->createWriteOnlyStream();
        $stream->getContents();
    }

    public function testGetContentsThrowsExceptionWhenStreamGetContentsFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to read stream contents.');

        $this->adapter->method('streamGetContents')->willReturn(false);

        $stream = $this->createReadWriteStream();
        $stream->getContents();
    }

    public function testGettingMetadata(): void
    {
        // Configureer de mock om zowel de volledige array als specifieke sleutels terug te geven
        $metadata = ['uri' => 'php://temp', 'mode' => 'r+', 'seekable' => true];

        $mockAdapter = $this->createMock(StreamAdapter::class);
        $mockAdapter->method('isResource')->willReturn(true);
        $mockAdapter->method('streamGetMetaData')->willReturn($metadata);

        $stream = new Stream(fopen('php://temp', 'r+'), $mockAdapter);

        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
    }

    public function testGetMetadataReturnsEmptyArrayWhenStreamIsDetached(): void
    {
        $stream = $this->createReadWriteStream();
        $stream->detach();

        $this->assertEquals([], $stream->getMetadata());
        $this->assertNull($stream->getMetadata('uri'));
    }

    public function testEofReturnsFalseWhenStreamNotAtEnd(): void
    {
        $this->adapter->method('fwrite')->willReturn(11);
        $this->adapter->method('rewind')->willReturn(true);
        $this->adapter->method('feof')->willReturn(false);

        $stream = $this->createReadWriteStream();
        $stream->write('Testing eof');
        $stream->rewind();

        $this->assertFalse($stream->eof());
    }

    public function testEofReturnsTrueWhenStreamAtEnd(): void
    {
        $this->adapter->method('fwrite')->willReturn(11);
        $this->adapter->method('rewind')->willReturn(true);
        $this->adapter->method('streamGetContents')->willReturn('Testing eof');
        $this->adapter->method('feof')->willReturn(true);

        $stream = $this->createReadWriteStream();
        $stream->write('Testing eof');
        $stream->__toString();

        $this->assertTrue($stream->eof());
    }

    public function testEofReturnsTrueForDetachedStream(): void
    {
        $stream = $this->createReadWriteStream();
        $stream->detach();

        $this->assertTrue($stream->eof());
    }
}
