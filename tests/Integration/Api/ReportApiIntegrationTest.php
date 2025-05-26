<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Laposta;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;

class ReportApiIntegrationTest extends BaseIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setListIdForTests('report');

        // Create a campaign to potentially get a report for
        $createCampaignData = [
            'type' => 'regular',
            'name' => 'Report Test Campaign - ' . self::generateRandomString(),
            'subject' => 'Report Test Subject',
            'from' => [
                'name' => 'Test Sender Name',
                'email' => self::getApprovedSenderAddress(),
            ],
            'list_ids' => [self::$listIdForTests],
        ];

        $apiKey = self::getApiKey();
        $laposta = new Laposta($apiKey);
        $campaignResponse = $laposta->campaignApi()->create($createCampaignData);
        self::$campaignIdForTests = $campaignResponse['campaign']['campaign_id'];
    }

    public static function tearDownAfterClass(): void
    {
        $apiKey = self::getApiKey();
        $laposta = new Laposta($apiKey);

        if (!empty(self::$campaignIdForTests)) {
            try {
                $laposta->campaignApi()->delete(self::$campaignIdForTests);
            } catch (ApiException $e) {
                fwrite(STDERR, "Cleanup error (campaign for report): " . $e->getMessage() . "\n");
            }
        }

        self::cleanupListForTests();
        parent::tearDownAfterClass();
    }

    public function testGetReportForCampaign(): void
    {
        if (empty(self::$campaignIdForTests)) {
            $this->markTestSkipped(
                'Campaign ID for report test is not available. '
                . 'Campaign creation might have failed.'
            );
        }

        try {
            $this->laposta->reportApi()->get(self::$campaignIdForTests);
            $this->fail('Een ApiException werd verwacht maar niet ontvangen');
        } catch (ApiException $e) {
            // Check response error message
            $responseBody = $e->getResponseBody();
            $this->assertNotEmpty($responseBody);
            $responseData = json_decode($responseBody, true);
            $this->assertIsArray($responseData);
            $this->assertArrayHasKey('error', $responseData);
            $this->assertArrayHasKey('message', $responseData['error']);
            $this->assertEquals('Campaign has not been sent', $responseData['error']['message']);
            $this->assertEquals('campaign_id', $responseData['error']['parameter']);
        }
    }

    public function testGetAllReports(): void
    {
        $response = $this->laposta->reportApi()->all();

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);

        // Reports list might be empty in a test account, especially if no campaigns have been sent.
        if (!empty($response['data'])) {
            $firstReportEntry = $response['data'][0];
            $this->assertArrayHasKey('report', $firstReportEntry, "Report entry should have a 'report' key.");
            $reportData = $firstReportEntry['report'];
            $this->assertArrayHasKey('campaign_id', $reportData);
            $this->assertArrayHasKey('opened_ratio', $reportData);
        }
    }
}
