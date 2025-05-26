<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration;

use LapostaApi\Exception\ApiException;
use LapostaApi\Laposta;
use PHPUnit\Framework\TestCase;

class BaseIntegrationTestCase extends TestCase
{
    protected Laposta $laposta;
    protected string $apiKey;
    protected string $approvedSenderAddress;

    protected static string $listIdForTests;
    protected static string $campaignIdForTests;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = $this->getApiKey();
        $this->approvedSenderAddress = $this->getApprovedSenderAddress();
        $this->laposta = new Laposta($this->apiKey);
    }

    protected static function getApiKey(): string
    {
        $val = $_ENV['LAPOSTA_API_KEY'] ?? null;
        if ($val === null) {
            throw new \Exception('LAPOSTA_API_KEY environment variable is not set.');
        }

        return $val;
    }

    protected static function getApprovedSenderAddress(): string
    {
        $val = $_ENV['APPROVED_SENDER_ADDRESS'] ?? null;
        if ($val === null) {
            throw new \Exception('APPROVED_SENDER_ADDRESS environment variable is not set.');
        }

        return $val;
    }

    protected static function generateRandomString(int $length = 10): string
    {
        return substr(str_shuffle(str_repeat(
            '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            (int)ceil($length / 62)
        )), 1, $length);
    }

    protected static function setListIdForTests(string $resource): void
    {
        $apiKey = self::getApiKey();
        $laposta = new Laposta($apiKey);

        $listName = "Test List for $resource- " . self::generateRandomString();
        $createListData = [
            'name' => $listName,
            'from_email' => "$resource@example.com",
            'from_name' => "$resource name",
            'remarks' => "List for $resource integration tests",
        ];
        try {
            $listResponse = $laposta->listApi()->create($createListData);
            self::$listIdForTests = $listResponse['list']['list_id'];
        } catch (\Exception $e) {
            throw new \Exception("Failed to create list for $resource tests: " . $e->getMessage());
        }
    }

    protected static function cleanupListForTests(): void
    {
        if (!empty(self::$listIdForTests)) {
            $apiKey = self::getApiKey();
            $laposta = new Laposta($apiKey);
            try {
                $laposta->listApi()->delete(self::$listIdForTests);
            } catch (ApiException $e) {
                fwrite(STDERR, "Cleanup error: " . $e->getMessage() . "\n");
            }
        }
    }
}
