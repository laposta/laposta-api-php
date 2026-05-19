<?php

declare(strict_types=1);

namespace LapostaApi\Query;

use InvalidArgumentException;

/**
 * Immutable cursor-pagination helpers for query parameter objects.
 */
trait CursorPaginationTrait
{
    private ?int $limit = null;
    private ?string $cursor = null;

    /**
     * Set the page size.
     *
     * The API currently accepts values from 1 through 1000. Pass null to clear the limit.
     */
    public function withLimit(?int $limit): static
    {
        if ($limit !== null && ($limit < 1 || $limit > 1000)) {
            throw new InvalidArgumentException('limit must be between 1 and 1000.');
        }

        $query = clone $this;
        $query->limit = $limit;

        return $query;
    }

    /**
     * Set an opaque cursor returned by a previous response.
     *
     * Most callers can use `HistoryResult::next()` instead of setting cursors manually.
     * Pass null to clear the cursor.
     */
    public function withCursor(?string $cursor): static
    {
        $query = clone $this;
        $query->cursor = $cursor === null ? null : QueryParameterHelper::nonEmptyString($cursor, 'cursor');

        return $query;
    }

    /**
     * Return pagination query parameters for the HTTP request.
     *
     * @return array<string, int|string>
     */
    private function paginationQueryParams(): array
    {
        return QueryParameterHelper::withoutNullValues([
            'limit' => $this->limit,
            'cursor' => $this->cursor,
        ]);
    }
}
