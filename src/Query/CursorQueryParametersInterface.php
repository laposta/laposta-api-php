<?php

declare(strict_types=1);

namespace LapostaApi\Query;

/**
 * Query parameter object that can continue from an opaque API cursor.
 */
interface CursorQueryParametersInterface extends QueryParametersInterface
{
    /**
     * Return a copy of the query with the given cursor, or without a cursor when null is passed.
     */
    public function withCursor(?string $cursor): static;
}
