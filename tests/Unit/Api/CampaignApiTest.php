<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\CampaignApi;

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
