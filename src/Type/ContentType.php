<?php

declare(strict_types=1);

namespace LapostaApi\Type;

enum ContentType: string
{
    case JSON = 'application/json';
    case FORM = 'application/x-www-form-urlencoded';

    /**
     * Processes the body according to the content type
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
