<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
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
