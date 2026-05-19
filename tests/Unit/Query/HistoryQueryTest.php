<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Query;

use DateTimeImmutable;
use InvalidArgumentException;
use LapostaApi\Query\CampaignHistoryQuery;
use LapostaApi\Query\CursorPaginationTrait;
use LapostaApi\Query\CursorQueryParametersInterface;
use LapostaApi\Query\MemberHistoryQuery;
use LapostaApi\Query\QueryParameterHelper;
use LapostaApi\Type\SortOrder;
use PHPUnit\Framework\TestCase;

class HistoryQueryTest extends TestCase
{
    public function testMemberHistoryQueryBuildsKnownParameters(): void
    {
        $query = MemberHistoryQuery::create()
            ->withCampaignId('camp123')
            ->withEventDateFrom('2026-01-01 00:00:00')
            ->withEventDateTo('2026-02-01 00:00:00')
            ->withLimit(100)
            ->withEventDateOrder(SortOrder::ASC)
            ->withCursor('opaque-cursor');

        $this->assertSame(
            [
                'limit' => 100,
                'cursor' => 'opaque-cursor',
                'order_by' => 'event_date',
                'order' => 'asc',
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
                'campaign_id' => 'camp123',
            ],
            $query->toQueryParams(),
        );
    }

    public function testHistoryQueriesAreImmutable(): void
    {
        $query = CampaignHistoryQuery::create();
        $changedQuery = $query->withLimit(10);

        $this->assertSame([], $query->toQueryParams());
        $this->assertSame(['limit' => 10], $changedQuery->toQueryParams());
    }

    public function testDateTimeInterfaceValuesKeepOffset(): void
    {
        $query = CampaignHistoryQuery::create()
            ->withEventDateFrom(new DateTimeImmutable('2026-01-01T12:30:00+02:00'));

        $this->assertSame(
            ['event_date_from' => '2026-01-01T12:30:00+02:00'],
            $query->toQueryParams(),
        );
    }

    public function testInvalidLimitThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CampaignHistoryQuery::create()->withLimit(1001);
    }

    public function testCursorPaginationBuildsParametersAndStaysImmutable(): void
    {
        $query = new class implements CursorQueryParametersInterface {
            use CursorPaginationTrait;

            public function toQueryParams(): array
            {
                return $this->paginationQueryParams();
            }
        };

        $changedQuery = $query->withLimit(25)->withCursor('opaque-cursor');

        $this->assertSame([], $query->toQueryParams());
        $this->assertSame(
            [
                'limit' => 25,
                'cursor' => 'opaque-cursor',
            ],
            $changedQuery->toQueryParams(),
        );
    }

    public function testCampaignHistoryQueryBuildsKnownParameters(): void
    {
        $query = CampaignHistoryQuery::create()
            ->withEventDateFrom('2026-01-01 00:00:00')
            ->withEventDateTo('2026-02-01 00:00:00')
            ->withEventDateOrder('DESC')
            ->withLimit(50);

        $this->assertSame(
            [
                'limit' => 50,
                'order_by' => 'event_date',
                'order' => 'desc',
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
            ],
            $query->toQueryParams(),
        );
    }

    public function testQueryParameterHelperFormatsDateTimeWithOffset(): void
    {
        $this->assertSame(
            '2026-02-01T00:00:00+02:00',
            QueryParameterHelper::dateTime(new DateTimeImmutable('2026-02-01T00:00:00+02:00'), 'event_date_to'),
        );
    }

    public function testQueryParameterHelperRejectsEmptyStrings(): void
    {
        $this->expectException(InvalidArgumentException::class);

        QueryParameterHelper::nonEmptyString('', 'campaign_id');
    }

    public function testQueryParameterHelperNormalisesSortOrder(): void
    {
        $this->assertSame(SortOrder::DESC, QueryParameterHelper::sortOrder('DESC', 'event_date_order'));
    }

    public function testQueryParameterHelperRemovesNullValues(): void
    {
        $this->assertSame(
            [
                'limit' => 0,
                'order' => 'desc',
            ],
            QueryParameterHelper::withoutNullValues([
                'limit' => 0,
                'cursor' => null,
                'order' => 'desc',
            ]),
        );
    }

    public function testConcreteQueriesCanClearOptionalParametersWithNull(): void
    {
        $query = MemberHistoryQuery::create()
            ->withCampaignId('camp123')
            ->withEventDateFrom('2026-01-01 00:00:00')
            ->withEventDateTo('2026-02-01 00:00:00')
            ->withEventDateOrder(SortOrder::DESC)
            ->withLimit(10)
            ->withCursor('opaque-cursor')
            ->withCampaignId(null)
            ->withEventDateFrom(null)
            ->withEventDateTo(null)
            ->withEventDateOrder(null)
            ->withLimit(null)
            ->withCursor(null);

        $this->assertSame([], $query->toQueryParams());
    }
}
