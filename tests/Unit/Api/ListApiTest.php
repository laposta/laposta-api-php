<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\ListApi;
use LapostaApi\Type\ContentType;

class ListApiTest extends BaseTestCase
{
    protected ListApi $listApi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listApi = new ListApi($this->laposta);
    }

    public function testGetList(): void
    {
        $listId = 'list123';
        $responseData = [
            'id' => $listId,
            'name' => 'Test List',
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-01-01 12:00:00',
        ];

        $this->executeApiTest(
            fn() => $this->listApi->get($listId),
            200,
            $responseData,
            'GET',
            "/list/$listId",
            null,
            $responseData,
        );
    }

    public function testCreateList(): void
    {
        $listData = [
            'name' => 'New List',
            'remarks' => 'Test list for unit tests',
        ];

        $responseData = [
            'id' => 'list456',
            'name' => 'New List',
            'remarks' => 'Test list for unit tests',
            'created' => '2023-01-02 10:00:00',
            'modified' => '2023-01-02 10:00:00',
        ];

        $this->executeApiTest(
            fn() => $this->listApi->create($listData),
            201,
            $responseData,
            'POST',
            '/list',
            $listData,
            $responseData,
        );
    }

    public function testUpdateList(): void
    {
        $listId = 'list123';
        $listData = [
            'name' => 'Updated List',
            'remarks' => 'Updated remarks',
        ];

        $responseData = [
            'id' => $listId,
            'name' => 'Updated List',
            'remarks' => 'Updated remarks',
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-01-03 15:30:00',
        ];

        $this->executeApiTest(
            fn() => $this->listApi->update($listId, $listData),
            200,
            $responseData,
            'POST',
            "/list/$listId",
            $listData,
            $responseData,
        );
    }

    public function testDeleteList(): void
    {
        $listId = 'list123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->listApi->delete($listId),
            200,
            $responseData,
            'DELETE',
            "/list/$listId",
            null,
            $responseData,
        );
    }

    public function testGetAllLists(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => 'list123',
                    'name' => 'List 1',
                    'created' => '2023-01-01 12:00:00',
                ],
                [
                    'id' => 'list456',
                    'name' => 'List 2',
                    'created' => '2023-01-02 10:00:00',
                ],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->listApi->all(),
            200,
            $responseData,
            'GET',
            '/list',
            null,
            $responseData,
        );

        // Additional verification for this test
        $this->assertCount(2, $result['data']);
    }

    public function testPurgeList(): void
    {
        $listId = 'list123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->listApi->purgeMembers($listId),
            200,
            $responseData,
            'DELETE',
            "/list/$listId/members",
            null,
            $responseData,
        );
    }

    public function testBulkOperation(): void
    {
        $listId = 'list123';
        $bulkData = [
            'members' => [
                [
                    'email' => 'john@example.com',
                    'state' => 'active',
                    'custom_fields' => [
                        'name' => 'John Doe',
                    ],
                ],
                [
                    'email' => 'jane@example.com',
                    'state' => 'active',
                    'custom_fields' => [
                        'name' => 'Jane Smith',
                    ],
                ],
            ],
        ];

        $responseData = [
            'success' => true,
            'results' => [
                'success' => 2,
                'error' => 0,
            ],
        ];

        // Use executeApiTest with JSON content type enum
        $this->executeApiTest(
            fn() => $this->listApi->addOrUpdateMembers($listId, $bulkData),
            200,
            $responseData,
            'POST',
            "/list/$listId/members",
            $bulkData,
            $responseData,
            ContentType::JSON,
        );
    }
}
