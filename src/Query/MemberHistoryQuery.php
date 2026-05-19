<?php

declare(strict_types=1);

namespace LapostaApi\Query;

use DateTimeInterface;
use LapostaApi\Type\SortOrder;

/**
 * Query parameters for `MemberApi::history()`.
 *
 * Member history returns events for one member in one list, including
 * subscription, modification, delivery, and engagement events. Use the campaign
 * filter to limit results to events that belong to one campaign.
 */
class MemberHistoryQuery implements CursorQueryParametersInterface
{
    use CursorPaginationTrait;

    private const ORDER_BY_EVENT_DATE = 'event_date';

    private ?string $campaignId = null;
    private ?string $eventDateFrom = null;
    private ?string $eventDateTo = null;
    private ?string $orderBy = null;
    private ?SortOrder $eventDateOrder = null;

    /**
     * Start a new immutable member history query.
     */
    public static function create(): static
    {
        return new static();
    }

    /**
     * Limit member history to events for a single campaign.
     *
     * Pass null to clear the campaign filter.
     */
    public function withCampaignId(?string $campaignId): static
    {
        $query = clone $this;
        $query->campaignId = $campaignId === null ? null : QueryParameterHelper::nonEmptyString(
            $campaignId,
            'campaign_id'
        );

        return $query;
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
                'campaign_id' => $this->campaignId,
            ]),
        );
    }
}
