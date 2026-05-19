<?php

declare(strict_types=1);

namespace LapostaApi\Result;

use Closure;
use LapostaApi\Query\CursorQueryParametersInterface;
use RuntimeException;

/**
 * Represents one cursor-paginated history response page.
 *
 * Instances are returned by `MemberApi::history()` and `CampaignApi::history()`.
 * Use `events()` for the current page and `next()` to fetch the following page
 * with the same filters, ordering, and limit.
 */
class HistoryResult
{
    /**
     * Create a history result page.
     *
     * Applications normally receive this from an API method rather than constructing it directly.
     *
     * @param array<string, mixed> $responseData
     * @param Closure(CursorQueryParametersInterface): self $fetchNext
     */
    public function __construct(
        private array $responseData,
        private CursorQueryParametersInterface $query,
        private Closure $fetchNext,
    ) {
    }

    /**
     * Return the decoded API response data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->responseData;
    }

    /**
     * Return the events in the current response page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function events(): array
    {
        $events = $this->responseData['data'] ?? [];

        return is_array($events) ? $events : [];
    }

    /**
     * Return the pagination metadata for the current history page.
     *
     * This is the decoded `pagination` response block. Use
     * `hasMore()`, `nextCursor()`, and `next()` for the common continuation
     * workflow.
     *
     * @return array<string, mixed>
     */
    public function pagination(): array
    {
        return $this->paginationData();
    }

    /**
     * Return the ordering metadata reported by the API for this history page.
     *
     * @return array<string, mixed>
     */
    public function order(): array
    {
        $order = $this->responseData['order'] ?? [];

        return is_array($order) ? $order : [];
    }

    /**
     * Return the filters reported by the API for this history page.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $filters = $this->responseData['filters'] ?? [];

        return is_array($filters) ? $filters : [];
    }

    /**
     * Whether another history page is available.
     */
    public function hasMore(): bool
    {
        return ($this->paginationData()['has_more'] ?? false) === true;
    }

    /**
     * Return the opaque cursor for the next page, if the API returned one.
     */
    public function nextCursor(): ?string
    {
        $cursor = $this->paginationData()['next_cursor'] ?? null;

        return is_string($cursor) && $cursor !== '' ? $cursor : null;
    }

    /**
     * Fetch the next history page using the original query with the next cursor.
     *
     * Returns null when the current page has no continuation.
     */
    public function next(): ?self
    {
        $cursor = $this->nextCursor();
        if (!$this->hasMore() || $cursor === null) {
            return null;
        }

        $fetchNext = $this->fetchNext;
        $result = $fetchNext($this->query->withCursor($cursor));
        if (!$result instanceof self) {
            throw new RuntimeException('History continuation did not return a history result.');
        }

        return $result;
    }

    private function paginationData(): array
    {
        $pagination = $this->responseData['pagination'] ?? [];

        return is_array($pagination) ? $pagination : [];
    }
}
