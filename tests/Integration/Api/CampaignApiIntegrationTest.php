<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Laposta;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;

class CampaignApiIntegrationTest extends BaseIntegrationTestCase
{
    private string $createdCampaignId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setListIdForTests('campaign');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::cleanupListForTests();
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdCampaignId)) {
            try {
                $this->laposta->campaignApi()->delete($this->createdCampaignId);
            } catch (ApiException $e) {
                fwrite(STDERR, "Cleanup error (campaign): " . $e->getMessage() . "\n");
            }
        }
        parent::tearDown();
    }

    /**
     * Creates data for a new campaign with the right parameters
     *
     * @param array $customData Custom data to override the default values
     *
     * @return array Data for creating a campaign
     */
    protected function createCampaignData(array $customData = []): array
    {
        $defaultData = [
            'type' => 'regular',
            'name' => 'Test Campaign - ' . $this->generateRandomString(),
            'subject' => 'Test Subject - ' . $this->generateRandomString(),
            'from' => [
                'name' => 'Test Sender Name',
                'email' => $this->getApprovedSenderAddress(),
            ],
            'list_ids' => [self::$listIdForTests],
        ];

        return array_merge($defaultData, $customData);
    }

    /**
     * Helper method to create a campaign with content for testing actions
     *
     * @return string The ID of the created campaign
     */
    protected function createCampaignWithContent(): string
    {
        // Create a campaign
        $campaignName = 'Content Test Campaign - ' . $this->generateRandomString();
        $createData = $this->createCampaignData([
            'name' => $campaignName,
            'subject' => 'laposta-api-php - ' . Laposta::VERSION . ' - ' . date('Y-m-d H:i:s'),
        ]);

        $createdCampaign = $this->laposta->campaignApi()->create($createData);
        $campaignId = $createdCampaign['campaign']['campaign_id'];

        // Add content to the campaign
        $contentData = [
            'html' => '<html lang="en"><body><h1>Test Content</h1>'
                . '<p>This is a test email for integration testing.</p></body></html>',
        ];

        $this->laposta->campaignApi()->updateContent($campaignId, $contentData);

        return $campaignId;
    }

    public function testCreateCampaign(): void
    {
        $campaignName = 'Test Campaign - ' . $this->generateRandomString();
        $subject = 'Test Subject - ' . $this->generateRandomString();
        $data = $this->createCampaignData([
            'name' => $campaignName,
            'subject' => $subject,
        ]);

        $response = $this->laposta->campaignApi()->create($data);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertArrayHasKey('campaign_id', $campaignData);
        $this->assertEquals($campaignName, $campaignData['name']);
        $this->assertEquals($subject, $campaignData['subject']);
        $this->createdCampaignId = $campaignData['campaign_id'];
    }

    public function testGetCampaign(): void
    {
        $campaignName = 'Get Test Campaign - ' . $this->generateRandomString();
        $subject = 'Get Test Subject - ' . $this->generateRandomString();
        $createData = $this->createCampaignData([
            'name' => $campaignName,
            'subject' => $subject,
        ]);

        $createdCampaign = $this->laposta->campaignApi()->create($createData);
        $this->createdCampaignId = $createdCampaign['campaign']['campaign_id'];

        $response = $this->laposta->campaignApi()->get($this->createdCampaignId);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertEquals($this->createdCampaignId, $campaignData['campaign_id']);
        $this->assertEquals($campaignName, $campaignData['name']);
    }

    public function testUpdateCampaign(): void
    {
        $campaignNameOriginal = 'Update Original Campaign - ' . $this->generateRandomString();
        $subjectOriginal = 'Update Original Subject - ' . $this->generateRandomString();
        $createData = $this->createCampaignData([
            'name' => $campaignNameOriginal,
            'subject' => $subjectOriginal,
        ]);

        $createdCampaign = $this->laposta->campaignApi()->create($createData);
        $this->createdCampaignId = $createdCampaign['campaign']['campaign_id'];

        $updatedName = 'Updated Campaign Name - ' . $this->generateRandomString();
        $updatedSubject = 'Updated Subject - ' . $this->generateRandomString();
        $updateData = [
            'name' => $updatedName,
            'subject' => $updatedSubject,
        ];

        $response = $this->laposta->campaignApi()->update($this->createdCampaignId, $updateData);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertEquals($this->createdCampaignId, $campaignData['campaign_id']);
        $this->assertEquals($updatedName, $campaignData['name']);
        $this->assertEquals($updatedSubject, $campaignData['subject']);
    }

    public function testDeleteCampaign(): void
    {
        $campaignName = 'Delete Test Campaign - ' . $this->generateRandomString();
        $createData = $this->createCampaignData([
            'name' => $campaignName,
            'subject' => 'Delete Subject',
        ]);

        $createdCampaign = $this->laposta->campaignApi()->create($createData);
        $campaignIdToDelete = $createdCampaign['campaign']['campaign_id'];

        $response = $this->laposta->campaignApi()->delete($campaignIdToDelete);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertArrayHasKey('state', $campaignData);
        $this->assertEquals('deleted', $campaignData['state']);

        // Verify it's actually deleted
        $this->expectException(ApiException::class);
        $this->laposta->campaignApi()->get($campaignIdToDelete);
    }

    public function testGetAllCampaigns(): void
    {
        $campaignName = 'List All Test Campaign - ' . $this->generateRandomString();
        $createData = $this->createCampaignData([
            'name' => $campaignName,
            'subject' => 'List All Subject',
        ]);

        $createdCampaignResponse = $this->laposta->campaignApi()->create($createData);
        $this->createdCampaignId = $createdCampaignResponse['campaign']['campaign_id'];

        $response = $this->laposta->campaignApi()->all();

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $found = false;
        foreach ($response['data'] as $campaignEntry) {
            $this->assertArrayHasKey('campaign', $campaignEntry);
            if ($campaignEntry['campaign']['campaign_id'] === $this->createdCampaignId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created campaign not found in all campaigns response.');
    }

    public function testGetAndUpdateContent(): void
    {
        // Create a campaign
        $campaignName = 'Content Test Campaign - ' . $this->generateRandomString();
        $createData = $this->createCampaignData([
            'name' => $campaignName,
            'subject' => 'Content Test Subject',
        ]);

        $createdCampaign = $this->laposta->campaignApi()->create($createData);
        $this->createdCampaignId = $createdCampaign['campaign']['campaign_id'];

        // Add content to the campaign and verify it
        $contentData = [
            'html' => '<html lang="en"><body><h1>Test Content</h1><p>This is a test email.</p></body></html>',
            'text' => 'Test Content\n\nThis is a test email.',
        ];
        $response = $this->laposta->campaignApi()->updateContent($this->createdCampaignId, $contentData);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertArrayHasKey('plaintext', $campaignData);
        $this->assertArrayHasKey('html', $campaignData);
        $this->assertStringContainsString('<h1>Test Content</h1>', $campaignData['html']);

        // Get the content and verify it
        $response = $this->laposta->campaignApi()->getContent($this->createdCampaignId);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertArrayHasKey('plaintext', $campaignData);
        $this->assertArrayHasKey('html', $campaignData);
        $this->assertStringContainsString('<h1>Test Content</h1>', $campaignData['html']);
    }

    public function testSendTestMail(): void
    {
        $this->createdCampaignId = $this->createCampaignWithContent();

        // Send test mail
        $response = $this->laposta->campaignApi()->sendTestMail($this->createdCampaignId, $this->approvedSenderAddress);

        $this->assertArrayHasKey('campaign', $response);
        $this->assertEquals($this->createdCampaignId, $response['campaign']['campaign_id']);
    }

    public function testScheduleCampaign(): void
    {
        $this->createdCampaignId = $this->createCampaignWithContent();

        // Schedule campaign for future delivery
        $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));
        $response = $this->laposta->campaignApi()->schedule($this->createdCampaignId, $tomorrow);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertEquals($this->createdCampaignId, $campaignData['campaign_id']);

        // Check if delivery_requested is set
        $this->assertArrayHasKey('delivery_requested', $campaignData);
        $this->assertSame($tomorrow, $campaignData['delivery_requested']);
    }

    public function testSendCampaign(): void
    {
        $this->createdCampaignId = $this->createCampaignWithContent();

        // Send the campaign directly
        $response = $this->laposta->campaignApi()->send($this->createdCampaignId);

        $this->assertArrayHasKey('campaign', $response);
        $campaignData = $response['campaign'];
        $this->assertEquals($this->createdCampaignId, $campaignData['campaign_id']);

        // Status should be 'sending' or 'sent'
        $this->assertNotEmpty($campaignData['delivery_requested']);
    }
}
