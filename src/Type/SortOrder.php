<?php

declare(strict_types=1);

namespace LapostaApi\Type;

/**
 * Generic sort direction for API query parameters.
 */
enum SortOrder: string
{
    /**
     * Sort from oldest or smallest value to newest or largest value.
     */
    case ASC = 'asc';

    /**
     * Sort from newest or largest value to oldest or smallest value.
     */
    case DESC = 'desc';
}
