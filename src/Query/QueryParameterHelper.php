<?php

declare(strict_types=1);

namespace LapostaApi\Query;

use DateTimeInterface;
use InvalidArgumentException;
use LapostaApi\Type\SortOrder;
use ValueError;

/**
 * Shared validation and formatting for typed query parameter objects.
 */
final class QueryParameterHelper
{
    /**
     * Trim and validate a required non-empty string query parameter.
     */
    public static function nonEmptyString(string $value, string $parameter): string
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException($parameter . ' cannot be empty.');
        }

        return $value;
    }

    /**
     * Format a date/time query parameter.
     *
     * String values are passed through unchanged after non-empty validation.
     * DateTimeInterface values are formatted with an explicit offset.
     */
    public static function dateTime(string|DateTimeInterface $value, string $parameter): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        return self::nonEmptyString($value, $parameter);
    }

    /**
     * Normalize a sort direction from an enum value or string.
     */
    public static function sortOrder(SortOrder|string $order, string $parameter): SortOrder
    {
        if ($order instanceof SortOrder) {
            return $order;
        }

        try {
            return SortOrder::from(strtolower(trim($order)));
        } catch (ValueError $e) {
            throw new InvalidArgumentException($parameter . ' must be asc or desc.', previous: $e);
        }
    }

    /**
     * Remove null values while preserving valid scalar values such as 0.
     *
     * @param array<string, int|string|null> $queryParams
     * @return array<string, int|string>
     */
    public static function withoutNullValues(array $queryParams): array
    {
        return array_filter(
            $queryParams,
            static fn($value): bool => $value !== null,
        );
    }
}
