<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;
use LapostaApi\Type\BulkMode;
use LapostaApi\Type\SyncAction;

class ListApiIntegrationTest extends BaseIntegrationTestCase
{
    private string $createdListId;

    protected function tearDown(): void
    {
        // Clean up created list if it exists
        if (!empty($this->createdListId)) {
            try {
                $this->laposta->listApi()->delete($this->createdListId);
            } catch (ApiException $e) {
                // Ignore if already deleted or other issues during cleanup
                fwrite(STDERR, "Cleanup error: " . $e->getMessage() . "\n");
            }
        }
        parent::tearDown();
    }

    public function testCreateList(): void
    {
        $listName = 'Test List - ' . $this->generateRandomString();
        $data = [
            'name' => $listName,
            // Add other required fields for list creation if any, e.g., from_email
            'from_email' => 'test@example.com',
            'from_name' => 'Test Sender',
            'remarks' => 'Integration test list',
        ];

        $response = $this->laposta->listApi()->create($data);

        $this->assertArrayHasKey('list', $response);
        $this->assertArrayHasKey('list_id', $response['list']);
        $this->assertEquals($listName, $response['list']['name']);
        $this->createdListId = $response['list']['list_id']; // Save for cleanup and other tests
    }

    public function testGetList(): void
    {
        // First, create a list to get
        $listName = 'Test List Get - ' . $this->generateRandomString();
        $createData = [
            'name' => $listName,
            'from_email' => 'testget@example.com',
            'from_name' => 'Test Sender Get',
            'remarks' => 'Integration test list for get operation',
        ];
        $createdList = $this->laposta->listApi()->create($createData);
        $this->createdListId = $createdList['list']['list_id'];

        $response = $this->laposta->listApi()->get($this->createdListId);

        $this->assertArrayHasKey('list', $response);
        $this->assertEquals($this->createdListId, $response['list']['list_id']);
        $this->assertEquals($listName, $response['list']['name']);
    }

    public function testUpdateList(): void
    {
        // First, create a list to update
        $listNameOriginal = 'Test List Update Original - ' . $this->generateRandomString();
        $createData = [
            'name' => $listNameOriginal,
            'from_email' => 'testupdate@example.com',
            'from_name' => 'Test Sender Update',
            'remarks' => 'Integration test list for update operation',
        ];
        $createdList = $this->laposta->listApi()->create($createData);
        $this->createdListId = $createdList['list']['list_id'];

        $updatedName = 'Test List Updated - ' . $this->generateRandomString();
        $updateData = [
            'name' => $updatedName,
            'remarks' => 'Updated remarks',
        ];

        $response = $this->laposta->listApi()->update($this->createdListId, $updateData);

        $this->assertArrayHasKey('list', $response);
        $this->assertEquals($this->createdListId, $response['list']['list_id']);
        $this->assertEquals($updatedName, $response['list']['name']);
        $this->assertEquals('Updated remarks', $response['list']['remarks']);
    }

    public function testDeleteList(): void
    {
        // First, create a list to delete
        $listName = 'Test List Delete - ' . $this->generateRandomString();
        $createData = [
            'name' => $listName,
            'from_email' => 'testdelete@example.com',
            'from_name' => 'Test Sender Delete',
            'remarks' => 'Integration test list for delete operation',
        ];
        $createdList = $this->laposta->listApi()->create($createData);
        $listIdToDelete = $createdList['list']['list_id'];

        $response = $this->laposta->listApi()->delete($listIdToDelete);
        $this->assertArrayHasKey('list', $response);
        $entityData = $response['list'];
        $this->assertIsArray($entityData);
        $this->assertArrayHasKey('state', $entityData);
        $this->assertEquals('deleted', $entityData['state']);

        // Verify it's actually deleted by trying to get it (should throw ApiException)
        $this->expectException(ApiException::class);
        $this->laposta->listApi()->get($listIdToDelete);
    }

    public function testGetAllLists(): void
    {
        // Create a list to ensure there's at least one
        $listName = 'Test List For All - ' . $this->generateRandomString();
        $createData = [
            'name' => $listName,
            'from_email' => 'testall@example.com',
            'from_name' => 'Test Sender All',
            'remarks' => 'Integration test list for get all operation',
        ];
        $createdList = $this->laposta->listApi()->create($createData);
        $this->createdListId = $createdList['list']['list_id']; // Mark for cleanup

        $response = $this->laposta->listApi()->all();

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']); // Assuming the account is not completely empty

        // Optionally, find the created list in the response
        $found = false;
        foreach ($response['data'] as $list) {
            if ($list['list']['list_id'] === $this->createdListId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created list not found in all lists response.');
    }

    public function testPurgeMembers(): void
    {
        // First, create a list for testing member purging
        $listName = 'Test List Purge Members - ' . $this->generateRandomString();
        $createData = [
            'name' => $listName,
            'from_email' => 'testpurge@example.com',
            'from_name' => 'Test Purge Members',
            'remarks' => 'Integration test list for purge members operation',
        ];
        $createdList = $this->laposta->listApi()->create($createData);
        $this->createdListId = $createdList['list']['list_id'];

        // Test purge members functionality
        $response = $this->laposta->listApi()->purgeMembers($this->createdListId);

        // Verify the response structure
        $this->assertArrayHasKey('list', $response);
        $this->assertArrayHasKey('list_id', $response['list']);
        $this->assertEquals($this->createdListId, $response['list']['list_id']);

        // Verify that the list contains the members count structure
        $this->assertArrayHasKey('members', $response['list']);
        $this->assertArrayHasKey('active', $response['list']['members']);
        $this->assertArrayHasKey('unsubscribed', $response['list']['members']);
        $this->assertArrayHasKey('cleaned', $response['list']['members']);

        // Verify that all member counts are 0 after purge
        $this->assertEquals(0, $response['list']['members']['active']);
        $this->assertEquals(0, $response['list']['members']['unsubscribed']);
        $this->assertEquals(0, $response['list']['members']['cleaned']);
    }

    /**
     * @deprecated Legacy bulk endpoint with the deprecated mode parameter.
     */
    public function testAddOrUpdateMembers(): void
    {
        // First, create a list for testing member operations
        $listName = 'Test List Add/Update Members - ' . $this->generateRandomString();
        $createData = [
            'name' => $listName,
            'from_email' => 'testaddupdate@example.com',
            'from_name' => 'Test Add/Update Members',
            'remarks' => 'Integration test list for add/update members operation',
        ];
        $createdList = $this->laposta->listApi()->create($createData);
        $this->createdListId = $createdList['list']['list_id'];

        // Prepare member data - include required fields based on your list configuration
        $data = [
            'mode' => BulkMode::ADD_AND_EDIT,
            'members' =>
                [
                    [
                        'email' => 'test1@example.com',
                    ],
                    [
                        'email' => 'test2@example.com',
                    ],
                    [
                        'email' => 'error',
                    ],
                ],
        ];

        // Test add members functionality
        $response = $this->laposta->listApi()->addOrUpdateMembers($this->createdListId, $data);

        // Verify the report structure exists
        $this->assertArrayHasKey('report', $response);

        // Verify that the report contains count fields
        $this->assertArrayHasKey('provided_count', $response['report']);
        $this->assertArrayHasKey('errors_count', $response['report']);
        $this->assertArrayHasKey('skipped_count', $response['report']);
        $this->assertArrayHasKey('edited_count', $response['report']);
        $this->assertArrayHasKey('added_count', $response['report']);
        $this->assertArrayHasKey('unsubscribed_count', $response['report']);

        // Verify that we have the arrays for different categories of members
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('skipped', $response);
        $this->assertArrayHasKey('edited', $response);
        $this->assertArrayHasKey('added', $response);

        // Verify the counts
        $this->assertEquals(3, $response['report']['provided_count']);
        $this->assertEquals(1, $response['report']['errors_count']);
        $this->assertEquals(0, $response['report']['skipped_count']);
        $this->assertEquals(0, $response['report']['edited_count']);
        $this->assertEquals(2, $response['report']['added_count']);
        $this->assertEquals(0, $response['report']['unsubscribed_count']);

        // Verify that the number of items in each array matches the reported counts
        $this->assertCount($response['report']['errors_count'], $response['errors']);
        $this->assertCount($response['report']['skipped_count'], $response['skipped']);
        $this->assertCount($response['report']['edited_count'], $response['edited']);
        $this->assertCount($response['report']['added_count'], $response['added']);
    }

    public function testSyncMembers(): void
    {
        $listName = 'Test List Sync Members - ' . $this->generateRandomString();
        $createData = [
            'name' => $listName,
            'from_email' => 'testsync@example.com',
            'from_name' => 'Test Sync Members',
            'remarks' => 'Integration test list for sync members operation',
        ];
        $createdList = $this->laposta->listApi()->create($createData);
        $this->createdListId = $createdList['list']['list_id'];

        $memberToUnsubscribeEmail = 'sync-remove-' . $this->generateRandomString() . '@example.com';
        $createdMember = $this->laposta->memberApi()->create($this->createdListId, [
            'email' => $memberToUnsubscribeEmail,
            'ip' => '123.123.123.200',
        ]);
        $memberIdToUnsubscribe = $createdMember['member']['member_id'];

        $data = [
            'actions' => [
                SyncAction::ADD,
                SyncAction::UPDATE,
                SyncAction::UNSUBSCRIBE_EXCLUDED, // members not present in 'members' will be unsubscribed
            ],
            'members' => [
                [
                    'email' => 'sync1@example.com',
                ],
                [
                    'email' => 'sync2@example.com',
                ],
                [
                    'email' => 'error',
                ],
            ],
        ];

        $response = $this->laposta->listApi()->syncMembers($this->createdListId, $data);

        $this->assertArrayHasKey('report', $response);
        $this->assertArrayHasKey('provided_count', $response['report']);
        $this->assertArrayHasKey('errors_count', $response['report']);
        $this->assertArrayHasKey('skipped_count', $response['report']);
        $this->assertArrayHasKey('edited_count', $response['report']);
        $this->assertArrayHasKey('added_count', $response['report']);
        $this->assertArrayHasKey('unsubscribed_count', $response['report']);

        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('skipped', $response);
        $this->assertArrayHasKey('edited', $response);
        $this->assertArrayHasKey('added', $response);

        $this->assertEquals(3, $response['report']['provided_count']);
        $this->assertEquals(1, $response['report']['errors_count']);
        $this->assertEquals(0, $response['report']['skipped_count']);
        $this->assertEquals(0, $response['report']['edited_count']);
        $this->assertEquals(2, $response['report']['added_count']);
        $this->assertEquals(1, $response['report']['unsubscribed_count']);

        $this->assertCount($response['report']['errors_count'], $response['errors']);
        $this->assertCount($response['report']['skipped_count'], $response['skipped']);
        $this->assertCount($response['report']['edited_count'], $response['edited']);
        $this->assertCount($response['report']['added_count'], $response['added']);

        $memberAfterSync = $this->laposta->memberApi()->get($this->createdListId, $memberIdToUnsubscribe);
        $this->assertEquals('unsubscribed', $memberAfterSync['member']['state']);
    }
}
