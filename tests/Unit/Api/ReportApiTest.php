<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\ReportApi;

class ReportApiTest extends BaseTestCase
{
    protected ReportApi $reportApi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportApi = new ReportApi($this->laposta);
    }

    public function testGetReport(): void
    {
        $campaignId = 'campaign123';
        $responseData = [
            'id' => $campaignId,
            'stats' => [
                'sent' => 1000,
                'delivered' => 980,
            ],
        ];

        $this->executeApiTest(
            fn() => $this->reportApi->get($campaignId),
            200,
            $responseData,
            'GET',
            "/report/$campaignId",
            null,
            $responseData,
        );
    }

    public function testGetAllReports(): void
    {
        $responseData = [
            'data' => [
                ['id' => 'campaign123'],
                ['id' => 'campaign456'],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->reportApi->all(),
            200,
            $responseData,
            'GET',
            '/report',
            null,
            $responseData,
        );

        // Verify there are reports in the response
        $this->assertCount(2, $result['data']);
    }
}
