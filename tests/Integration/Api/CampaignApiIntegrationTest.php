<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
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
     * Maakt data voor een nieuwe campagne met de juiste parameters
     *
     * @param array $customData Aangepaste data om de standaardwaarden te overschrijven
     *
     * @return array Data voor het aanmaken van een campagne
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
        $createdCampaignIdForThisTest = $createdCampaignResponse['campaign']['campaign_id'];

        $response = $this->laposta->campaignApi()->all();

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $found = false;
        foreach ($response['data'] as $campaignEntry) {
            $this->assertArrayHasKey('campaign', $campaignEntry);
            if ($campaignEntry['campaign']['campaign_id'] === $createdCampaignIdForThisTest) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created campaign not found in all campaigns response.');

        // Ruim de campagne op die specifiek voor deze test is gemaakt
        try {
            $this->laposta->campaignApi()->delete($createdCampaignIdForThisTest);
        } catch (ApiException $e) {
            fwrite(STDERR, "Cleanup error (campaign): " . $e->getMessage() . "\n");
        }
    }
}
