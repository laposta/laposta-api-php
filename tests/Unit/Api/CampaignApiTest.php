<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use DateTimeImmutable;
use DateTimeInterface;
use GuzzleHttp\Psr7\Response;
use LapostaApi\Api\CampaignApi;
use LapostaApi\Query\CampaignHistoryQuery;
use LapostaApi\Result\HistoryResult;
use LapostaApi\Type\SortOrder;

class CampaignApiTest extends BaseTestCase
{
    protected CampaignApi $campaignApi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->campaignApi = new CampaignApi($this->laposta);
    }

    public function testGetCampaign(): void
    {
        $campaignId = 'campaign123';
        $responseData = ['id' => $campaignId, 'name' => 'Test Campaign'];

        $this->executeApiTest(
            fn() => $this->campaignApi->get($campaignId),
            200,
            $responseData,
            'GET',
            "/campaign/$campaignId",
            null,
            $responseData,
        );
    }

    public function testCreateCampaign(): void
    {
        $campaignData = [
            'name' => 'New Campaign',
            'list_id' => 'list123',
            'subject' => 'Test Subject',
        ];

        $responseData = ['id' => 'campaign456', 'name' => 'New Campaign'];

        $this->executeApiTest(
            fn() => $this->campaignApi->create($campaignData),
            201,
            $responseData,
            'POST',
            '/campaign',
            $campaignData,
            $responseData,
        );
    }

    public function testUpdateCampaign(): void
    {
        $campaignId = 'campaign123';
        $campaignData = [
            'name' => 'Updated Campaign',
            'subject' => 'New Subject',
        ];

        $responseData = ['id' => $campaignId, 'name' => 'Updated Campaign'];

        $this->executeApiTest(
            fn() => $this->campaignApi->update($campaignId, $campaignData),
            200,
            $responseData,
            'POST',
            "/campaign/$campaignId",
            $campaignData,
            $responseData,
        );
    }

    public function testDeleteCampaign(): void
    {
        $campaignId = 'campaign123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->campaignApi->delete($campaignId),
            200,
            $responseData,
            'DELETE',
            "/campaign/$campaignId",
            null,
            $responseData,
        );
    }

    public function testGetAllCampaigns(): void
    {
        $responseData = [
            'data' => [
                ['id' => 'campaign123', 'name' => 'Campaign 1'],
                ['id' => 'campaign456', 'name' => 'Campaign 2'],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->campaignApi->all(),
            200,
            $responseData,
            'GET',
            '/campaign',
            null,
            $responseData,
        );

        // Extra verification specific to this test
        $this->assertCount(2, $result['data']);
    }

    public function testGetCampaignHistory(): void
    {
        $campaignId = 'campaign123';
        $query = CampaignHistoryQuery::create()
            ->withEventDateFrom('2026-01-01 00:00:00')
            ->withEventDateTo('2026-02-01 00:00:00')
            ->withLimit(25)
            ->withEventDateOrder(SortOrder::DESC);
        $responseData = [
            'pagination' => [
                'limit' => 25,
                'has_more' => false,
                'next_cursor' => null,
            ],
            'order' => [
                'order_by' => 'event_date',
                'order' => 'desc',
            ],
            'filters' => [
                'timezone' => 'Europe/Amsterdam',
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
            ],
            'data' => [
                [
                    'event' => [
                        'type' => 'sent',
                        'timezone' => 'Europe/Amsterdam',
                        'event_date' => '2026-01-15 10:00:00',
                        'event_date_iso' => '2026-01-15T10:00:00+01:00',
                        'campaign' => [
                            'campaign_id' => $campaignId,
                            'name' => 'January newsletter',
                            'from_automation' => false,
                            'deleted' => false,
                        ],
                        'list' => null,
                        'member' => null,
                        'details' => null,
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(new Response(200, [], json_encode($responseData)));

        $result = $this->campaignApi->history($campaignId, $query);

        $this->verifyRequest(
            'GET',
            "/campaign/$campaignId/history",
            expectedQueryParams: [
                'limit' => 25,
                'order_by' => 'event_date',
                'order' => 'desc',
                'event_date_from' => '2026-01-01 00:00:00',
                'event_date_to' => '2026-02-01 00:00:00',
            ],
        );

        $this->assertInstanceOf(HistoryResult::class, $result);
        $this->assertSame($responseData, $result->toArray());
        $this->assertSame($responseData['pagination'], $result->pagination());
        $this->assertSame($responseData['order'], $result->order());
        $this->assertSame($responseData['filters'], $result->filters());
        $this->assertCount(1, $result->events());
    }

    public function testCampaignHistoryNextKeepsQueryContextAndAddsCursor(): void
    {
        $campaignId = 'campaign123';
        $query = CampaignHistoryQuery::create()
            ->withEventDateFrom('2026-01-01 00:00:00')
            ->withEventDateTo('2026-02-01 00:00:00')
            ->withLimit(1);
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

        $firstResult = $this->campaignApi->history($campaignId, $query);
        $secondResult = $firstResult->next();

        $this->assertInstanceOf(HistoryResult::class, $secondResult);
        $this->assertSame([['event' => ['type' => 'opened']]], $secondResult->events());
        $this->assertCount(2, $this->history);

        parse_str($this->history[1]['request']->getUri()->getQuery(), $queryParams);
        $this->assertSame('1', $queryParams['limit']);
        $this->assertSame('2026-01-01 00:00:00', $queryParams['event_date_from']);
        $this->assertSame('2026-02-01 00:00:00', $queryParams['event_date_to']);
        $this->assertSame('opaque-cursor-1', $queryParams['cursor']);
    }

    public function testGetCampaignContent(): void
    {
        $campaignId = 'campaign123';
        $responseData = [
            'id' => $campaignId,
            'content' => [
                'html' => '<p>Test content</p>',
                'text' => 'Test content',
            ],
        ];

        $this->executeApiTest(
            fn() => $this->campaignApi->getContent($campaignId),
            200,
            $responseData,
            'GET',
            "/campaign/$campaignId/content",
            null,
            $responseData,
        );
    }

    public function testAddCampaignContent(): void
    {
        $campaignId = 'campaign123';
        $contentData = [
            'html' => '<p>New content</p>',
            'text' => 'New content',
        ];

        $responseData = ['id' => $campaignId, 'success' => true];

        $this->executeApiTest(
            fn() => $this->campaignApi->updateContent($campaignId, $contentData),
            200,
            $responseData,
            'POST',
            "/campaign/$campaignId/content",
            $contentData,
            $responseData,
        );
    }

    public function testSendCampaign(): void
    {
        $campaignId = 'campaign123';
        $responseData = ['id' => $campaignId, 'success' => true];

        $this->executeApiTest(
            fn() => $this->campaignApi->send($campaignId),
            200,
            $responseData,
            'POST',
            "/campaign/$campaignId/action/send",
            null,
            $responseData,
        );
    }

    public function testScheduleCampaign(): void
    {
        $campaignId = 'campaign123';
        $deliveryRequested = '2023-12-31 14:30:00';
        $responseData = ['id' => $campaignId, 'success' => true];

        $this->executeApiTest(
            fn() => $this->campaignApi->schedule($campaignId, $deliveryRequested),
            200,
            $responseData,
            'POST',
            "/campaign/$campaignId/action/schedule",
            ['delivery_requested' => $deliveryRequested],
            $responseData,
        );
    }

    public function testScheduleCampaignAcceptsDateTime(): void
    {
        $campaignId = 'campaign123';
        $deliveryRequested = new DateTimeImmutable('2023-12-31T14:30:00+01:00');
        $responseData = ['id' => $campaignId, 'success' => true];

        $this->executeApiTest(
            fn() => $this->campaignApi->schedule($campaignId, $deliveryRequested),
            200,
            $responseData,
            'POST',
            "/campaign/$campaignId/action/schedule",
            ['delivery_requested' => $deliveryRequested->format(DateTimeInterface::ATOM)],
            $responseData,
        );
    }

    public function testSendTestMail(): void
    {
        $campaignId = 'campaign123';
        $email = 'test@example.com';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->campaignApi->sendTestMail($campaignId, $email),
            200,
            $responseData,
            'POST',
            "/campaign/$campaignId/action/testmail",
            ['email' => $email],
            $responseData,
        );
    }
}
