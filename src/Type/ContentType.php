<?php

declare(strict_types=1);

namespace LapostaApi\Type;

/**
 * Enum representing content types for HTTP requests.
 *
 * This enum defines supported content types used in API communication
 * and provides functionality to format request bodies accordingly.
 */
enum ContentType: string
{
    /**
     * JSON content type for structured data.
     * Used when sending JSON-formatted request bodies.
     */
    case JSON = 'application/json';

    /**
     * Form URL-encoded content type.
     * Used when sending form data in request bodies.
     */
    case FORM = 'application/x-www-form-urlencoded';

    /**
     * Processes the body according to the content type.
     *
     * Formats the provided data array into a string representation
     * matching the current content type.
     *
     * @param array $data The data to be formatted
     *
     * @return string The formatted body
     * @throws \JsonException If JSON encoding fails
     */
    public function formatBody(array $data): string
    {
        return match ($this) {
            self::JSON => json_encode($data, JSON_THROW_ON_ERROR),
            self::FORM => http_build_query($data),
        };
    }
}
