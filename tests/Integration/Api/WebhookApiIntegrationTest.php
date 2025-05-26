<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;

class WebhookApiIntegrationTest extends BaseIntegrationTestCase
{
    private string $createdWebhookId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setListIdForTests('webhook');
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanupListForTests();
        parent::tearDownAfterClass();
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdWebhookId) && !empty(self::$listIdForTests)) {
            try {
                $this->laposta->webhookApi()->delete(self::$listIdForTests, $this->createdWebhookId);
            } catch (ApiException $e) {
                fwrite(STDERR, "Cleanup error (webhook): " . $e->getMessage() . "\n");
            }
        }
        parent::tearDown();
    }

    /**
     * Creates data for a new webhook with the correct parameters
     *
     * @param array $customData Custom data to override default values
     *
     * @return array Data for creating a webhook
     */
    protected function createWebhookData(array $customData = []): array
    {
        $defaultData = [
            'list_id' => self::$listIdForTests,
            'event' => 'subscribed',
            'url' => 'https://example.com/webhook-' . $this->generateRandomString(),
            'blocked' => false,
        ];

        return array_merge($defaultData, $customData);
    }

    public function testCreateWebhook(): void
    {
        $url = 'https://example.com/webhook-' . $this->generateRandomString();
        $data = $this->createWebhookData([
            'url' => $url,
            'event' => 'subscribed',
        ]);

        $response = $this->laposta->webhookApi()->create(self::$listIdForTests, $data);
        $this->assertArrayHasKey('webhook', $response);
        $webhookData = $response['webhook'];
        $this->assertArrayHasKey('webhook_id', $webhookData);
        $this->assertEquals($url, $webhookData['url']);
        $this->assertEquals('subscribed', $webhookData['event']);
        $this->createdWebhookId = $webhookData['webhook_id'];
    }

    public function testGetWebhook(): void
    {
        $url = 'https://example.com/get-webhook-' . $this->generateRandomString();
        $createData = $this->createWebhookData([
            'url' => $url,
            'event' => 'modified',
        ]);
        $createdWebhook = $this->laposta->webhookApi()->create(self::$listIdForTests, $createData);
        $this->createdWebhookId = $createdWebhook['webhook']['webhook_id'];

        $response = $this->laposta->webhookApi()->get(self::$listIdForTests, $this->createdWebhookId);

        $this->assertArrayHasKey('webhook', $response);
        $webhookData = $response['webhook'];
        $this->assertEquals($this->createdWebhookId, $webhookData['webhook_id']);
        $this->assertEquals($url, $webhookData['url']);
    }

    public function testUpdateWebhook(): void
    {
        $urlOriginal = 'https://example.com/update-orig-webhook-' . $this->generateRandomString();
        $createData = $this->createWebhookData([
            'url' => $urlOriginal,
            'event' => 'deactivated',
            'blocked' => false,
        ]);
        $createdWebhook = $this->laposta->webhookApi()->create(self::$listIdForTests, $createData);
        $this->createdWebhookId = $createdWebhook['webhook']['webhook_id'];

        $updatedUrl = 'https://example.com/updated-webhook-' . $this->generateRandomString();
        $updateData = [
            'url' => $updatedUrl,
            'blocked' => true,
        ];

        $response = $this->laposta->webhookApi()->update(
            self::$listIdForTests,
            $this->createdWebhookId,
            $updateData
        );

        $this->assertArrayHasKey('webhook', $response);
        $webhookData = $response['webhook'];
        $this->assertEquals($this->createdWebhookId, $webhookData['webhook_id']);
        $this->assertEquals($updatedUrl, $webhookData['url']);
        $this->assertTrue($webhookData['blocked']);
    }

    public function testDeleteWebhook(): void
    {
        $url = 'https://example.com/delete-webhook-' . $this->generateRandomString();
        $createData = $this->createWebhookData([
            'url' => $url,
            'event' => 'modified',
        ]);
        $createdWebhook = $this->laposta->webhookApi()->create(self::$listIdForTests, $createData);
        $webhookIdToDelete = $createdWebhook['webhook']['webhook_id'];

        $response = $this->laposta->webhookApi()->delete(self::$listIdForTests, $webhookIdToDelete);

        $this->assertArrayHasKey('webhook', $response);
        $webhookData = $response['webhook'];
        $this->assertArrayHasKey('state', $webhookData);
        $this->assertEquals('deleted', $webhookData['state']);

        // Verify it's actually deleted
        $this->expectException(ApiException::class);
        $this->laposta->webhookApi()->get(self::$listIdForTests, $webhookIdToDelete);
    }

    public function testGetAllWebhooks(): void
    {
        $url = 'https://example.com/list-all-webhook-' . $this->generateRandomString();
        $createData = $this->createWebhookData([
            'url' => $url,
            'event' => 'subscribed',
        ]);
        $createdWebhookResponse = $this->laposta->webhookApi()->create(self::$listIdForTests, $createData);
        $createdWebhookIdForThisTest = $createdWebhookResponse['webhook']['webhook_id'];

        $response = $this->laposta->webhookApi()->all(self::$listIdForTests);

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $found = false;
        foreach ($response['data'] as $webhookEntry) {
            $this->assertArrayHasKey('webhook', $webhookEntry);
            if ($webhookEntry['webhook']['webhook_id'] === $createdWebhookIdForThisTest) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created webhook not found in all webhooks response.');

        try {
            $this->laposta->webhookApi()->delete(self::$listIdForTests, $createdWebhookIdForThisTest);
        } catch (ApiException $e) {
            fwrite(STDERR, "Cleanup error: " . $e->getMessage() . "\n");
        }
    }
}
