<?php

declare(strict_types=1);

namespace LapostaApi\Query;

/**
 * Query parameter object that can be converted to HTTP query parameters.
 */
interface QueryParametersInterface
{
    /**
     * Return scalar query parameters for the HTTP request.
     *
     * @return array<string, int|string>
     */
    public function toQueryParams(): array;
}
