<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\WebhookApi;

class WebhookApiTest extends BaseTestCase
{
    protected WebhookApi $webhookApi;
    protected string $listId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listId = 'list123';
        $this->webhookApi = new WebhookApi($this->laposta);
    }

    public function testGetWebhook(): void
    {
        $webhookId = 'webhook123';
        $responseData = [
            'id' => $webhookId,
            'url' => 'https://example.com/webhook',
            'event' => 'subscribed',
        ];

        $this->executeApiTest(
            fn() => $this->webhookApi->get($this->listId, $webhookId),
            200,
            $responseData,
            'GET',
            "/webhook/$webhookId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testCreateWebhook(): void
    {
        $webhookData = [
            'url' => 'https://example.com/webhook',
            'event' => 'subscribed',
            'blocked' => false,
        ];

        $responseData = [
            'id' => 'webhook456',
            'url' => 'https://example.com/webhook',
            'event' => 'subscribed',
            'blocked' => false,
        ];

        // In the request, list_id should be added to the webhook data
        $expectedRequestData = $webhookData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->webhookApi->create($this->listId, $webhookData),
            201,
            $responseData,
            'POST',
            '/webhook',
            $expectedRequestData,
            $responseData,
        );
    }

    public function testUpdateWebhook(): void
    {
        $webhookId = 'webhook123';
        $webhookData = [
            'url' => 'https://example.com/updated-webhook',
            'blocked' => true,
        ];

        $responseData = [
            'id' => $webhookId,
            'url' => 'https://example.com/updated-webhook',
            'event' => 'subscribed',
            'blocked' => true,
        ];

        // In the request, list_id should be added to the webhook data
        $expectedRequestData = $webhookData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->webhookApi->update($this->listId, $webhookId, $webhookData),
            200,
            $responseData,
            'POST',
            "/webhook/$webhookId",
            $expectedRequestData,
            $responseData,
        );
    }

    public function testDeleteWebhook(): void
    {
        $webhookId = 'webhook123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->webhookApi->delete($this->listId, $webhookId),
            200,
            $responseData,
            'DELETE',
            "/webhook/$webhookId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testGetAllWebhooks(): void
    {
        $responseData = [
            'data' => [
                ['id' => 'webhook123', 'event' => 'subscribed'],
                ['id' => 'webhook456', 'event' => 'unsubscribed'],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->webhookApi->all($this->listId),
            200,
            $responseData,
            'GET',
            '/webhook',
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );

        // Verify there are webhooks in the response
        $this->assertCount(2, $result['data']);
    }
}
