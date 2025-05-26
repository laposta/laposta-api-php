<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\SegmentApi;

class SegmentApiTest extends BaseTestCase
{
    protected SegmentApi $segmentApi;
    protected string $listId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listId = 'list123';
        $this->segmentApi = new SegmentApi($this->laposta);
    }

    protected function createDefinition(string $field, string $operator, string $value): string
    {
        return json_encode([
            'type' => 'and',
            'items' => [
                [
                    'field' => $field,
                    'operator' => $operator,
                    'value' => $value
                ]
            ]
        ]);
    }

    public function testGetSegment(): void
    {
        $segmentId = 'segment123';
        $responseData = [
            'segment_id' => $segmentId,
            'list_id' => $this->listId,
            'name' => 'Active customers',
            'definition' => $this->createDefinition('custom_fields.status', 'equals', 'active'),
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-01-01 12:00:00'
        ];

        $this->executeApiTest(
            fn() => $this->segmentApi->get($this->listId, $segmentId),
            200,
            $responseData,
            'GET',
            "/segment/$segmentId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testCreateSegment(): void
    {
        $segmentData = [
            'name' => 'New customers',
            'definition' => $this->createDefinition('created', 'after', '2023-05-01')
        ];

        $responseData = [
            'segment_id' => 'segment456',
            'list_id' => $this->listId,
            'name' => 'New customers',
            'definition' => $this->createDefinition('created', 'after', '2023-05-01'),
            'created' => '2023-05-08 10:00:00',
            'modified' => '2023-05-08 10:00:00'
        ];

        // In the request, list_id should be added to the segment data
        $expectedRequestData = $segmentData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->segmentApi->create($this->listId, $segmentData),
            201,
            $responseData,
            'POST',
            '/segment',
            $expectedRequestData,
            $responseData,
        );
    }

    public function testUpdateSegment(): void
    {
        $segmentId = 'segment123';
        $segmentData = [
            'name' => 'Updated segment name',
            'definition' => $this->createDefinition('email', 'contains', 'example.com')
        ];

        $responseData = [
            'segment_id' => $segmentId,
            'list_id' => $this->listId,
            'name' => 'Updated segment name',
            'definition' => $this->createDefinition('email', 'contains', 'example.com'),
            'created' => '2023-01-01 12:00:00',
            'modified' => '2023-05-10 15:30:00'
        ];

        // In the request, list_id should be added to the segment data
        $expectedRequestData = $segmentData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->segmentApi->update($this->listId, $segmentId, $segmentData),
            200,
            $responseData,
            'POST',
            "/segment/$segmentId",
            $expectedRequestData,
            $responseData,
        );
    }

    public function testDeleteSegment(): void
    {
        $segmentId = 'segment123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->segmentApi->delete($this->listId, $segmentId),
            200,
            $responseData,
            'DELETE',
            "/segment/$segmentId",
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );
    }

    public function testGetAllSegments(): void
    {
        $responseData = [
            'data' => [
                [
                    'segment_id' => 'segment123',
                    'list_id' => $this->listId,
                    'name' => 'Active customers',
                    'definition' => $this->createDefinition('custom_fields.status', 'equals', 'active')
                ],
                [
                    'segment_id' => 'segment456',
                    'list_id' => $this->listId,
                    'name' => 'New customers',
                    'definition' => $this->createDefinition('created', 'after', '2023-05-01')
                ],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->segmentApi->all($this->listId),
            200,
            $responseData,
            'GET',
            '/segment',
            null,
            $responseData,
            expectedQueryParams: ['list_id' => $this->listId],
        );

        // Additional verification for this test
        $this->assertCount(2, $result['data']);
    }
}
