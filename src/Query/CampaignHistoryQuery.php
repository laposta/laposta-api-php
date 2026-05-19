<?php

declare(strict_types=1);

namespace LapostaApi\Query;

use DateTimeInterface;
use LapostaApi\Type\SortOrder;

/**
 * Query parameters for `CampaignApi::history()`.
 *
 * Campaign history returns member events that belong to the selected campaign,
 * such as sent, opened, clicked, bounced, and unsubscribe-related events. It
 * does not include ordinary campaign create/update actions.
 */
class CampaignHistoryQuery implements CursorQueryParametersInterface
{
    use CursorPaginationTrait;

    private const ORDER_BY_EVENT_DATE = 'event_date';

    private ?string $eventDateFrom = null;
    private ?string $eventDateTo = null;
    private ?string $orderBy = null;
    private ?SortOrder $eventDateOrder = null;

    /**
     * Start a new immutable campaign history query.
     */
    public static function create(): static
    {
        return new static();
    }

    /**
     * Set the inclusive lower event-date filter.
     *
     * String values are passed through unchanged and may use YYYY-MM-DD HH:MM:SS in the account timezone
     * or an explicit-offset ISO/RFC3339 datetime. DateTimeInterface values are formatted with their timezone
     * offset, including +00:00 for UTC.
     * Pass null to clear the filter.
     */
    public function withEventDateFrom(string|DateTimeInterface|null $eventDateFrom): static
    {
        $query = clone $this;
        $query->eventDateFrom = $eventDateFrom === null ? null : QueryParameterHelper::dateTime(
            $eventDateFrom,
            'event_date_from'
        );

        return $query;
    }

    /**
     * Set the exclusive upper event-date filter.
     *
     * String values are passed through unchanged and may use YYYY-MM-DD HH:MM:SS in the account timezone
     * or an explicit-offset ISO/RFC3339 datetime. DateTimeInterface values are formatted with their timezone
     * offset, including +00:00 for UTC.
     * Pass null to clear the filter.
     */
    public function withEventDateTo(string|DateTimeInterface|null $eventDateTo): static
    {
        $query = clone $this;
        $query->eventDateTo = $eventDateTo === null ? null : QueryParameterHelper::dateTime(
            $eventDateTo,
            'event_date_to'
        );

        return $query;
    }

    /**
     * Sort history by event date.
     *
     * Accepts `SortOrder::ASC`, `SortOrder::DESC`, "asc", or "desc".
     * Pass null to use the API default ordering.
     */
    public function withEventDateOrder(SortOrder|string|null $order): static
    {
        $query = clone $this;
        $query->orderBy = $order === null ? null : self::ORDER_BY_EVENT_DATE;
        $query->eventDateOrder = $order === null ? null : QueryParameterHelper::sortOrder(
            $order,
            'event_date_order'
        );

        return $query;
    }

    /**
     * Return query parameters for the HTTP request.
     *
     * @return array<string, int|string>
     */
    public function toQueryParams(): array
    {
        return array_merge(
            $this->paginationQueryParams(),
            QueryParameterHelper::withoutNullValues([
                'order_by' => $this->orderBy,
                'order' => $this->eventDateOrder?->value,
                'event_date_from' => $this->eventDateFrom,
                'event_date_to' => $this->eventDateTo,
            ]),
        );
    }
}
