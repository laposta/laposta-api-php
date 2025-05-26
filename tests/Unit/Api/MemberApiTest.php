<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\MemberApi;

class MemberApiTest extends BaseTestCase
{
    protected MemberApi $memberApi;
    protected string $listId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listId = 'list123';
        $this->memberApi = new MemberApi($this->laposta);
    }

    public function testGetMember(): void
    {
        $memberId = 'member123';
        $responseData = [
            'id' => $memberId,
            'email' => 'john@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'John Doe',
            ],
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-01-01 12:00:00',
        ];

        $this->executeApiTest(
            fn() => $this->memberApi->get($this->listId, $memberId),
            200,
            $responseData,
            'GET',
            "/member/$memberId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testCreateMember(): void
    {
        $memberData = [
            'email' => 'jane@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'Jane Smith',
                'age' => 28,
            ],
        ];

        $responseData = [
            'id' => 'member456',
            'email' => 'jane@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'Jane Smith',
                'age' => 28,
            ],
            'created' => '2023-01-02 10:00:00',
            'modified' => '2023-01-02 10:00:00',
        ];

        // In the request, list_id should be added to the member data
        $expectedRequestData = $memberData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->memberApi->create($this->listId, $memberData),
            201,
            $responseData,
            'POST',
            '/member',
            $expectedRequestData,
            $responseData,
        );
    }

    public function testUpdateMember(): void
    {
        $memberId = 'member123';
        $memberData = [
            'custom_fields' => [
                'name' => 'John Smith',
                'age' => 35,
            ],
        ];

        $responseData = [
            'id' => $memberId,
            'email' => 'john@example.com',
            'state' => 'active',
            'custom_fields' => [
                'name' => 'John Smith',
                'age' => 35,
            ],
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-01-03 15:30:00',
        ];

        // In the request, list_id should be added to the member data
        $expectedRequestData = $memberData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->memberApi->update($this->listId, $memberId, $memberData),
            200,
            $responseData,
            'POST',
            "/member/$memberId",
            $expectedRequestData,
            $responseData,
        );
    }

    public function testDeleteMember(): void
    {
        $memberId = 'member123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->memberApi->delete($this->listId, $memberId),
            200,
            $responseData,
            'DELETE',
            "/member/$memberId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testGetAllMembers(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => 'member123',
                    'email' => 'john@example.com',
                    'state' => 'active',
                ],
                [
                    'id' => 'member456',
                    'email' => 'jane@example.com',
                    'state' => 'active',
                ],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->memberApi->all($this->listId),
            200,
            $responseData,
            'GET',
            '/member',
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );

        // Additional verification for this test
        $this->assertCount(2, $result['data']);
    }
}
