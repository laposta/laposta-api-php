<?php

declare(strict_types=1);

namespace LapostaApi\Http;

use LapostaApi\Adapter\StreamAdapter;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * PSR-17 compatible stream factory implementation
 *
 * This implementation only provides createStream method as it's the only
 * method needed by the application. Other methods throw exceptions when called.
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * Creates a new StreamFactory instance.
     */
    public function __construct(
        protected StreamAdapter $adapter = new StreamAdapter()
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        // Create a temporary in-memory stream using the adapter
        $resource = $this->adapter->fopen('php://temp', 'r+');
        if ($resource === false) {
            throw new RuntimeException('Could not create temporary stream');
        }

        // Write content to the stream if provided
        if ($content !== '') {
            $this->adapter->fwrite($resource, $content);
            $this->adapter->rewind($resource);
        }

        return new Stream($resource, $this->adapter);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        throw new RuntimeException('Method createStreamFromFile() is not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        throw new RuntimeException('Method createStreamFromResource() is not implemented');
    }
}
