<?php

declare(strict_types=1);

namespace LapostaApi\Adapter;

/**
 * Adapter class for PHP's cURL functions.
 *
 * This class provides a simple wrapper around PHP's native cURL functions
 * to allow for easier testing through mocking of HTTP requests in the application.
 */
class CurlAdapter
{
    /**
     * Initialize a new cURL session.
     *
     * @return \CurlHandle|false A cURL handle on success, false on failure
     */
    public function init(): \CurlHandle|false
    {
        return curl_init();
    }

    /**
     * Set an option for a cURL transfer.
     *
     * @param \CurlHandle $handle The cURL handle
     * @param int $option The CURLOPT option to set
     * @param mixed $value The value to set the option to
     *
     * @return bool Returns true on success, false on failure
     */
    public function setopt(\CurlHandle $handle, int $option, mixed $value): bool
    {
        return curl_setopt($handle, $option, $value);
    }

    /**
     * Execute the cURL request.
     *
     * @param \CurlHandle $handle The cURL handle
     *
     * @return string|false Returns the result of the request as string on success, false on failure
     */
    public function exec(\CurlHandle $handle): string|false
    {
        return curl_exec($handle);
    }

    /**
     * Get information about the last transfer.
     *
     * @param \CurlHandle $handle The cURL handle
     * @param int $option The CURLINFO option to get, or 0 to get all info
     *
     * @return mixed The requested information or an array with all information if no option specified
     */
    public function getInfo(\CurlHandle $handle, int $option = 0): mixed
    {
        return curl_getinfo($handle, $option);
    }

    /**
     * Get the error code for the last cURL operation.
     *
     * @param \CurlHandle $handle The cURL handle
     *
     * @return int The error number or 0 if no error occurred
     */
    public function getErrno(\CurlHandle $handle): int
    {
        return curl_errno($handle);
    }

    /**
     * Get the error message for the last cURL operation.
     *
     * @param \CurlHandle $handle The cURL handle
     *
     * @return string The error message or an empty string if no error occurred
     */
    public function getError(\CurlHandle $handle): string
    {
        return curl_error($handle);
    }

    /**
     * Close a cURL session and free all resources.
     *
     * @param \CurlHandle $handle The cURL handle
     *
     * @return void
     */
    public function close(\CurlHandle $handle): void
    {
        curl_close($handle);
    }
}
