<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;

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
}
