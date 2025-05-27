<?php

declare(strict_types=1);

namespace LapostaApi\Adapter;

/**
 * Adapter class for PHP's file stream functions.
 *
 * This class provides a simple wrapper around PHP's native stream functions
 * to allow for easier testing through mocking of file operations in the application.
 */
class StreamAdapter
{
    /**
     * Opens a file or URL.
     *
     * @param string $filename The filename or URL to open
     * @param string $mode The mode for opening the file
     *
     * @return resource|false A file pointer resource on success, or false on failure
     */
    public function fopen(string $filename, string $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * Checks if a variable is a resource.
     *
     * @param mixed $value The value to check
     *
     * @return bool True if the value is a resource, false otherwise
     */
    public function isResource(mixed $value): bool
    {
        return is_resource($value);
    }

    /**
     * Reads up to a specified number of bytes from a stream.
     *
     * @param resource $stream The file pointer resource
     * @param int $length The number of bytes to read
     *
     * @return string|false The read string or false on failure
     */
    public function fread($stream, int $length): string|false
    {
        return fread($stream, $length);
    }

    /**
     * Writes to a stream.
     *
     * @param resource $stream The file pointer resource
     * @param string $string The string to write
     *
     * @return int|false The number of bytes written or false on failure
     */
    public function fwrite($stream, string $string): int|false
    {
        return fwrite($stream, $string);
    }

    /**
     * Gets information about a file using an open file pointer.
     *
     * @param resource $stream The file pointer resource
     *
     * @return array|false An array with file statistics or false on failure
     */
    public function fstat($stream): array|false
    {
        return fstat($stream);
    }

    /**
     * Gets the current position of the file pointer.
     *
     * @param resource $stream The file pointer resource
     *
     * @return int|false The position of the file pointer or false on error
     */
    public function ftell($stream): int|false
    {
        return ftell($stream);
    }

    /**
     * Tests for end-of-file on a file pointer.
     *
     * @param resource $stream The file pointer resource
     *
     * @return bool True if the file pointer is at EOF or an error occurs, false otherwise
     */
    public function feof($stream): bool
    {
        return feof($stream);
    }

    /**
     * Seeks on a file pointer.
     *
     * @param resource $stream The file pointer resource
     * @param int $offset The offset in bytes
     * @param int $whence The position from where to start seeking
     *
     * @return int Returns 0 on success, -1 on failure
     */
    public function fseek($stream, int $offset, int $whence = SEEK_SET): int
    {
        return fseek($stream, $offset, $whence);
    }

    /**
     * Rewinds a file pointer to the beginning.
     *
     * @param resource $stream The file pointer resource
     *
     * @return bool True on success, false on failure
     */
    public function rewind($stream): bool
    {
        return rewind($stream);
    }

    /**
     * Closes an open file pointer.
     *
     * @param resource $stream The file pointer resource
     *
     * @return bool True on success, false on failure
     */
    public function fclose($stream): bool
    {
        return fclose($stream);
    }

    /**
     * Reads the remaining contents from a stream into a string.
     *
     * @param resource $stream The file pointer resource
     *
     * @return string|false The read data or false on failure
     */
    public function streamGetContents($stream): string|false
    {
        return stream_get_contents($stream);
    }

    /**
     * Retrieves header/meta data from streams/file pointers.
     *
     * @param resource $stream The file pointer resource
     *
     * @return array Array containing metadata
     */
    public function streamGetMetaData($stream): array
    {
        return stream_get_meta_data($stream);
    }
}
