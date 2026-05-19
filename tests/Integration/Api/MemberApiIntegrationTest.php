<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Query\MemberHistoryQuery;
use LapostaApi\Result\HistoryResult;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;

class MemberApiIntegrationTest extends BaseIntegrationTestCase
{
    private string $createdMemberId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setListIdForTests('member');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::cleanupListForTests();
    }

    protected function tearDown(): void
    {
        // Clean up created member if it exists
        if (!empty($this->createdMemberId) && !empty(self::$listIdForTests)) {
            try {
                $this->laposta->memberApi()->delete(self::$listIdForTests, $this->createdMemberId);
            } catch (ApiException $e) {
                 fwrite(STDERR, 'Cleanup error (member): ' . $e->getMessage() . "\n");
            }
        }
        parent::tearDown();
    }

    public function testCreateMember(): void
    {
        $email = 'testmember-' . $this->generateRandomString() . '@example.com';
        $data = [
            'email' => $email,
            'ip' => '123.123.123.123', // Example IP
            'custom_fields' => [
                'name' => 'Test User',
                'city' => 'Test City',
            ],
        ];

        $response = $this->laposta->memberApi()->create(self::$listIdForTests, $data);

        $this->assertArrayHasKey('member', $response);
        $this->assertArrayHasKey('member_id', $response['member']);
        $this->assertEquals($email, $response['member']['email']);
        $this->createdMemberId = $response['member']['member_id'];
    }

    public function testGetMember(): void
    {
        $email = 'getmember-' . $this->generateRandomString() . '@example.com';
        $createData = [
            'email' => $email,
            'ip' => '123.123.123.124',
            'custom_fields' => ['name' => 'Get Test User'],
        ];
        $createdMember = $this->laposta->memberApi()->create(self::$listIdForTests, $createData);
        $this->createdMemberId = $createdMember['member']['member_id'];

        $response = $this->laposta->memberApi()->get(self::$listIdForTests, $this->createdMemberId);

        $this->assertArrayHasKey('member', $response);
        $this->assertEquals($this->createdMemberId, $response['member']['member_id']);
        $this->assertEquals($email, $response['member']['email']);
    }

    public function testGetMemberHistory(): void
    {
        $email = 'memberhistory-' . $this->generateRandomString() . '@example.com';
        $createData = [
            'email' => $email,
            'ip' => '123.123.123.128',
            'custom_fields' => ['name' => 'History Test User'],
        ];
        $createdMember = $this->laposta->memberApi()->create(self::$listIdForTests, $createData);
        $this->createdMemberId = $createdMember['member']['member_id'];

        $this->laposta->memberApi()->update(
            self::$listIdForTests,
            $this->createdMemberId,
            ['email' => 'memberhistory-' . $this->generateRandomString() . '@example.com'],
        );
        $this->laposta->memberApi()->update(
            self::$listIdForTests,
            $this->createdMemberId,
            ['email' => 'memberhistory-' . $this->generateRandomString() . '@example.com'],
        );

        $query = MemberHistoryQuery::create()->withLimit(2);
        [$response, $nextResponse] = $this->waitForMemberHistoryPages($this->createdMemberId, $query);

        $this->assertInstanceOf(HistoryResult::class, $response);
        $this->assertIsArray($response->toArray());
        $this->assertArrayHasKey('limit', $response->pagination());
        $this->assertSame(2, $response->pagination()['limit']);
        $this->assertArrayHasKey('has_more', $response->pagination());
        $this->assertArrayHasKey('next_cursor', $response->pagination());
        $this->assertIsArray($response->order());
        $this->assertIsArray($response->filters());
        $this->assertCount(2, $response->events());
        $this->assertTrue($response->hasMore());
        $this->assertNotNull($response->nextCursor());

        $this->assertInstanceOf(HistoryResult::class, $nextResponse);
        $this->assertCount(1, $nextResponse->events());
        $this->assertFalse($nextResponse->hasMore());
        $this->assertNull($nextResponse->nextCursor());
        $this->assertNull($nextResponse->next());
    }

    /**
     * @return array{0: HistoryResult, 1: HistoryResult}
     */
    private function waitForMemberHistoryPages(string $memberId, MemberHistoryQuery $query): array
    {
        $response = null;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->laposta->memberApi()->history(self::$listIdForTests, $memberId, $query);
            $nextResponse = $response->next();
            if (
                count($response->events()) === 2
                && $response->hasMore()
                && $nextResponse instanceof HistoryResult
                && count($nextResponse->events()) === 1
                && !$nextResponse->hasMore()
            ) {
                return [$response, $nextResponse];
            }

            usleep(250000);
        }

        $this->assertInstanceOf(HistoryResult::class, $response);
        $this->fail('Member history did not expose the expected two-page result.');
    }

    public function testUpdateMember(): void
    {
        $email = 'updatemember-' . $this->generateRandomString() . '@example.com';
        $createData = [
            'email' => $email,
            'ip' => '123.123.123.125',
        ];
        $createdMember = $this->laposta->memberApi()->create(self::$listIdForTests, $createData);
        $this->createdMemberId = $createdMember['member']['member_id'];


        $updateData = ['email' => 'updatemember-' . $this->generateRandomString() . '@example.com'];

        $response = $this->laposta->memberApi()->update(
            self::$listIdForTests,
            $this->createdMemberId,
            $updateData
        );

        $this->assertArrayHasKey('member', $response);
        $this->assertEquals($this->createdMemberId, $response['member']['member_id']);
        $this->assertEquals($updateData['email'], $response['member']['email']);
    }

    public function testDeleteMember(): void
    {
        $email = 'deletemember-' . $this->generateRandomString() . '@example.com';
        $createData = [
            'email' => $email,
            'ip' => '123.123.123.126',
        ];
        $createdMember = $this->laposta->memberApi()->create(self::$listIdForTests, $createData);
        $memberIdToDelete = $createdMember['member']['member_id'];

        $response = $this->laposta->memberApi()->delete(self::$listIdForTests, $memberIdToDelete);
        $this->assertArrayHasKey('member', $response);
        $entityData = $response['member'];
        $this->assertIsArray($entityData);
        $this->assertArrayHasKey('state', $entityData);
        $this->assertEquals('deleted', $entityData['state']);

        // Verify it's actually deleted by trying to get it (should throw ApiException)
        $this->expectException(ApiException::class);
        $this->laposta->memberApi()->get(self::$listIdForTests, $memberIdToDelete);
    }

    public function testGetAllMembers(): void
    {
        // Create a member to ensure the list is not empty for this test
        $email = 'getallmember-' . $this->generateRandomString() . '@example.com';
        $createData = [
            'email' => $email,
            'ip' => '123.123.123.127',
        ];
        $createdMember = $this->laposta->memberApi()->create(self::$listIdForTests, $createData);
        $this->createdMemberId = $createdMember['member']['member_id']; // Mark for cleanup

        $response = $this->laposta->memberApi()->all(self::$listIdForTests);

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $found = false;
        foreach ($response['data'] as $memberEntry) {
            if ($memberEntry['member']['member_id'] === $this->createdMemberId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created member not found in all members response.');
    }
}
