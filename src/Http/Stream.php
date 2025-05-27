<?php

declare(strict_types=1);

namespace LapostaApi\Http;

use LapostaApi\Adapter\StreamAdapter;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    protected $stream;

    protected StreamAdapter $adapter;

    /**
     * Initialize a new stream instance.
     *
     * @param resource $stream The PHP stream resource
     * @param StreamAdapter $adapter The adapter for stream operations
     *
     * @throws RuntimeException When $stream is not a valid resource
     */
    public function __construct($stream, StreamAdapter $adapter)
    {
        $this->adapter = $adapter;

        if (!$this->adapter->isResource($stream)) {
            throw new RuntimeException('Stream must be a valid resource.');
        }

        $this->stream = $stream;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        if (!$this->isReadable() || !$this->isSeekable()) {
            return '';
        }

        $this->rewind();
        return $this->adapter->streamGetContents($this->stream) ?: '';
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            $this->adapter->fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $result = $this->stream;
        $this->stream = null;
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        if (!$this->stream) {
            return null;
        }

        $stats = $this->adapter->fstat($this->stream);
        return $stats['size'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        if (!$this->stream) {
            throw new RuntimeException('No stream available.');
        }

        $result = $this->adapter->ftell($this->stream);

        if ($result === false) {
            throw new RuntimeException('Unable to determine position of pointer.');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function eof(): bool
    {
        return !$this->stream || $this->adapter->feof($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable(): bool
    {
        $meta = $this->getMetadata();
        return $meta['seekable'] ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable() || $this->adapter->fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Stream is not seekable.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        if (!$this->adapter->rewind($this->stream)) {
            throw new RuntimeException('Unable to rewind the stream.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable(): bool
    {
        $mode = $this->getMetadata('mode');
        return is_string($mode) && (str_contains($mode, 'w') || str_contains($mode, 'a') || str_contains($mode, '+'));
    }

    /**
     * {@inheritDoc}
     */
    public function write($string): int
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable.');
        }

        $result = $this->adapter->fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Failed to write to the stream.');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable(): bool
    {
        $mode = $this->getMetadata('mode');
        return is_string($mode) && (str_contains($mode, 'r') || str_contains($mode, '+'));
    }

    /**
     * {@inheritDoc}
     */
    public function read($length): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable.');
        }

        $result = $this->adapter->fread($this->stream, $length);
        if ($result === false) {
            throw new RuntimeException('Failed to read from the stream.');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable.');
        }

        $contents = $this->adapter->streamGetContents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents.');
        }

        return $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null): mixed
    {
        if (!$this->stream) {
            return $key === null ? [] : null;
        }

        $meta = $this->adapter->streamGetMetaData($this->stream);

        return $key === null ? $meta : ($meta[$key] ?? null);
    }
}
