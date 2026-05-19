<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use GuzzleHttp\Psr7\Response;
use LapostaApi\Api\MemberApi;
use LapostaApi\Query\MemberHistoryQuery;
use LapostaApi\Result\HistoryResult;
use LapostaApi\Type\SortOrder;

class MemberApiTest extends BaseTestCase
{
    protected MemberApi $memberApi;
    protected string $listId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listId = 'list123';
        $this->memberApi = new MemberApi($this->laposta);
    }

    public function testGetMember(): void
    {
        $memberId = 'member123';
        $responseData = [
            'id' => $memberId,
            'email' => 'john@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'John Doe',
            ],
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-01-01 12:00:00',
        ];

        $this->executeApiTest(
            fn() => $this->memberApi->get($this->listId, $memberId),
            200,
            $responseData,
            'GET',
            "/member/$memberId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testGetMemberHistory(): void
    {
        $memberId = 'member123';
        $responseData = [
            'pagination' => [
                'limit' => 100,
                'has_more' => false,
                'next_cursor' => null,
            ],
            'order' => [
                'order_by' => 'event_date',
                'order' => 'desc',
            ],
            'filters' => [],
            'data' => [
                [
                    'event' => [
                        'type' => 'sent',
                        'timezone' => 'Europe/Amsterdam',
                        'event_date' => '2026-04-16 09:12:44',
                        'event_date_iso' => '2026-04-16T09:12:44+02:00',
                        'campaign' => [
                            'campaign_id' => 'camp123',
                            'name' => 'April newsletter',
                            'from_automation' => false,
                            'deleted' => false,
                        ],
                        'details' => null,
                    ],
                ],
                [
                    'event' => [
                        'type' => 'clickedunique',
                        'timezone' => 'Europe/Amsterdam',
                        'event_date' => '2026-04-16 09:15:10',
                        'event_date_iso' => '2026-04-16T09:15:10+02:00',
                        'campaign' => [
                            'campaign_id' => 'camp123',
                            'name' => 'April newsletter',
                            'from_automation' => false,
                            'deleted' => false,
                        ],
                        'details' => [
                            'info1' => 12345,
                            'url' => 'https://example.com/article',
                        ],
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(new Response(200, [], json_encode($responseData)));

        $result = $this->memberApi->history($this->listId, $memberId);

        $this->verifyRequest(
            'GET',
            "/member/$memberId/history",
            expectedQueryParams: ['list_id' => $this->listId],
        );

        $this->assertInstanceOf(HistoryResult::class, $result);
        $this->assertSame($responseData, $result->toArray());
        $this->assertSame($responseData['pagination'], $result->pagination());
        $this->assertSame($responseData['order'], $result->order());
        $this->assertSame([], $result->filters());
        $this->assertCount(2, $result->events());
        $this->assertFalse($result->hasMore());
        $this->assertNull($result->next());
    }

    public function testGetMemberHistoryWithFiltersOrderAndPagination(): void
    {
        $memberId = 'member123';
        $campaignId = 'camp123';
        $query = MemberHistoryQuery::create()
            ->withCampaignId($campaignId)
            ->withEventDateFrom('2026-01-01 00:00:00')
            ->withEventDateTo('2026-02-01 00:00:00')
            ->withLimit(50)
            ->withEventDateOrder(SortOrder::ASC);

        $responseData = [
            'pagination' => [
                'limit' => 50,
                'has_more' => false,
                'next_cursor' => null,
            ],
            'order' => [
                'order_by' => 'event_date',
                'order' => 'asc',
            ],
            'filters' => [
                'timezone' => 'Europe/Amsterdam',
                'campaign_id' => $campaignId,
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
            ],
            'data' => [
                [
                    'event' => [
                        'type' => 'sent',
                        'timezone' => 'Europe/Amsterdam',
                        'event_date' => '2026-04-16 09:12:44',
                        'event_date_iso' => '2026-04-16T09:12:44+02:00',
                        'campaign' => [
                            'campaign_id' => $campaignId,
                            'name' => 'April newsletter',
                            'from_automation' => false,
                            'deleted' => false,
                        ],
                        'details' => null,
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(new Response(200, [], json_encode($responseData)));

        $result = $this->memberApi->history($this->listId, $memberId, $query);

        $this->verifyRequest(
            'GET',
            "/member/$memberId/history",
            expectedQueryParams: [
                'list_id' => $this->listId,
                'limit' => 50,
                'order_by' => 'event_date',
                'order' => 'asc',
                'campaign_id' => $campaignId,
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
            ],
        );

        $this->assertSame($responseData, $result->toArray());
        $this->assertSame($responseData['pagination'], $result->pagination());
        $this->assertSame($responseData['order'], $result->order());
        $this->assertSame($responseData['filters'], $result->filters());
    }

    public function testMemberHistoryNextKeepsQueryContextAndAddsCursor(): void
    {
        $memberId = 'member123';
        $campaignId = 'camp123';
        $query = MemberHistoryQuery::create()
            ->withCampaignId($campaignId)
            ->withEventDateFrom('2026-01-01 00:00:00')
            ->withEventDateTo('2026-02-01 00:00:00')
            ->withLimit(1)
            ->withEventDateOrder('desc');

        $firstResponse = [
            'pagination' => [
                'limit' => 1,
                'has_more' => true,
                'next_cursor' => 'opaque-cursor-1',
            ],
            'order' => [
                'order_by' => 'event_date',
                'order' => 'desc',
            ],
            'filters' => [
                'timezone' => 'Europe/Amsterdam',
                'campaign_id' => $campaignId,
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
            ],
            'data' => [
                ['event' => ['type' => 'sent']],
            ],
        ];
        $secondResponse = [
            'pagination' => [
                'limit' => 1,
                'has_more' => false,
                'next_cursor' => null,
            ],
            'order' => [
                'order_by' => 'event_date',
                'order' => 'desc',
            ],
            'filters' => [
                'timezone' => 'Europe/Amsterdam',
                'campaign_id' => $campaignId,
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
            ],
            'data' => [
                ['event' => ['type' => 'opened']],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($firstResponse)),
            new Response(200, [], json_encode($secondResponse)),
        );

        $firstResult = $this->memberApi->history($this->listId, $memberId, $query);
        $secondResult = $firstResult->next();

        $this->assertInstanceOf(HistoryResult::class, $secondResult);
        $this->assertSame([['event' => ['type' => 'opened']]], $secondResult->events());
        $this->assertCount(2, $this->history);

        parse_str($this->history[1]['request']->getUri()->getQuery(), $queryParams);
        $this->assertSame($this->listId, $queryParams['list_id']);
        $this->assertSame('1', $queryParams['limit']);
        $this->assertSame('event_date', $queryParams['order_by']);
        $this->assertSame('desc', $queryParams['order']);
        $this->assertSame($campaignId, $queryParams['campaign_id']);
        $this->assertSame('2026-01-01 00:00:00', $queryParams['event_date_from']);
        $this->assertSame('2026-02-01 00:00:00', $queryParams['event_date_to']);
        $this->assertSame('opaque-cursor-1', $queryParams['cursor']);
    }

    public function testCreateMember(): void
    {
        $memberData = [
            'email' => 'jane@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'Jane Smith',
                'age' => 28,
            ],
        ];

        $responseData = [
            'id' => 'member456',
            'email' => 'jane@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'Jane Smith',
                'age' => 28,
            ],
            'created' => '2023-01-02 10:00:00',
            'modified' => '2023-01-02 10:00:00',
        ];

        // In the request, list_id should be added to the member data
        $expectedRequestData = $memberData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->memberApi->create($this->listId, $memberData),
            201,
            $responseData,
            'POST',
            '/member',
            $expectedRequestData,
            $responseData,
        );
    }

    public function testUpdateMember(): void
    {
        $memberId = 'member123';
        $memberData = [
            'custom_fields' => [
                'name' => 'John Smith',
                'age' => 35,
            ],
        ];

        $responseData = [
            'id' => $memberId,
            'email' => 'john@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'John Smith',
                'age' => 35,
            ],
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-01-03 15:30:00',
        ];

        // In the request, list_id should be added to the member data
        $expectedRequestData = $memberData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->memberApi->update($this->listId, $memberId, $memberData),
            200,
            $responseData,
            'POST',
            "/member/$memberId",
            $expectedRequestData,
            $responseData,
        );
    }

    public function testDeleteMember(): void
    {
        $memberId = 'member123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->memberApi->delete($this->listId, $memberId),
            200,
            $responseData,
            'DELETE',
            "/member/$memberId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testGetAllMembers(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => 'member123',
                    'email' => 'john@example.com',
                    'state' => 'active',
                ],
                [
                    'id' => 'member456',
                    'email' => 'jane@example.com',
                    'state' => 'active',
                ],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->memberApi->all($this->listId),
            200,
            $responseData,
            'GET',
            '/member',
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );

        // Additional verification for this test
        $this->assertCount(2, $result['data']);
    }
}
