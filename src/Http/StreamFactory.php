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
     * Stream adapter used for creating stream resources
     */
    private StreamAdapter $adapter;

    /**
     * Constructor
     *
     * @param StreamAdapter|null $adapter Optional stream adapter, creates new one if not provided
     */
    public function __construct(?StreamAdapter $adapter = null)
    {
        $this->adapter = $adapter ?? new StreamAdapter();
    }

    /**
     * Create a new stream with the given content
     *
     * @param string $content String content to be used as stream content
     *
     * @return StreamInterface A stream containing the specified content
     * @throws RuntimeException If the temporary stream cannot be created
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
     * Create a stream from a file - NOT IMPLEMENTED
     *
     * @param string $filename Path to file
     * @param string $mode Mode used to open the file
     *
     * @return StreamInterface
     * @throws RuntimeException This method is not implemented
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        throw new RuntimeException('Method createStreamFromFile() is not implemented');
    }

    /**
     * Create a new stream from an existing resource - NOT IMPLEMENTED
     *
     * @param resource $resource PHP resource to create stream from
     *
     * @return StreamInterface
     * @throws RuntimeException This method is not implemented
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        throw new RuntimeException('Method createStreamFromResource() is not implemented');
    }
}
