<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;

class SegmentApiIntegrationTest extends BaseIntegrationTestCase
{
    private string $createdSegmentId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setListIdForTests('segment');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::cleanupListForTests();
    }

    protected function tearDown(): void
    {
        // Clean up created segment if it exists
        if (!empty($this->createdSegmentId) && !empty(self::$listIdForTests)) {
            try {
                $this->laposta->segmentApi()->delete(self::$listIdForTests, $this->createdSegmentId);
            } catch (ApiException $e) {
                 fwrite(STDERR, 'Cleanup error (segment): ' . $e->getMessage() . "\n");
            }
        }
        parent::tearDown();
    }

    protected function createDefinition(string $date = '2025-05-07'): string
    {
        return json_encode([
            'blocks' => [
                [
                    'rules' => [
                        [
                            'input' => [
                                'subject' => 'signup',
                                'comparator' => 'equal',
                                'multi' => '',
                                'text' => $date
                            ],
                            'type' => 'members'
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testCreateSegment(): void
    {
        $segmentName = 'Test Segment ' . $this->generateRandomString();
        $data = [
            'name' => $segmentName,
            'definition' => $this->createDefinition(),
        ];

        $response = $this->laposta->segmentApi()->create(self::$listIdForTests, $data);

        $this->assertArrayHasKey('segment', $response);
        $this->assertArrayHasKey('segment_id', $response['segment']);
        $this->assertEquals($segmentName, $response['segment']['name']);
        $this->createdSegmentId = $response['segment']['segment_id'];
    }

    public function testGetSegment(): void
    {
        $segmentName = 'Get Test Segment ' . $this->generateRandomString();
        $createData = [
            'name' => $segmentName,
            'definition' => $this->createDefinition(),
        ];
        $createdSegment = $this->laposta->segmentApi()->create(self::$listIdForTests, $createData);
        $this->createdSegmentId = $createdSegment['segment']['segment_id'];

        $response = $this->laposta->segmentApi()->get(self::$listIdForTests, $this->createdSegmentId);

        $this->assertArrayHasKey('segment', $response);
        $this->assertEquals($this->createdSegmentId, $response['segment']['segment_id']);
        $this->assertEquals($segmentName, $response['segment']['name']);
    }

    public function testUpdateSegment(): void
    {
        $segmentName = 'Update Test Segment ' . $this->generateRandomString();
        $createData = [
            'name' => $segmentName,
            'definition' => $this->createDefinition(),
        ];
        $createdSegment = $this->laposta->segmentApi()->create(self::$listIdForTests, $createData);
        $this->createdSegmentId = $createdSegment['segment']['segment_id'];

        $updatedName = 'Updated Segment ' . $this->generateRandomString();
        $updateData = [
            'name' => $updatedName,
            'definition' => $this->createDefinition('2025-05-08'),
        ];

        $response = $this->laposta->segmentApi()->update(
            self::$listIdForTests,
            $this->createdSegmentId,
            $updateData
        );

        $this->assertArrayHasKey('segment', $response);
        $this->assertEquals($this->createdSegmentId, $response['segment']['segment_id']);
        $this->assertEquals($updatedName, $response['segment']['name']);
    }

    public function testDeleteSegment(): void
    {
        $segmentName = 'Delete Test Segment ' . $this->generateRandomString();
        $createData = [
            'name' => $segmentName,
            'definition' => $this->createDefinition(),
        ];
        $createdSegment = $this->laposta->segmentApi()->create(self::$listIdForTests, $createData);
        $segmentIdToDelete = $createdSegment['segment']['segment_id'];

        $response = $this->laposta->segmentApi()->delete(self::$listIdForTests, $segmentIdToDelete);
        $this->assertArrayHasKey('segment', $response);
        $entityData = $response['segment'];
        $this->assertIsArray($entityData);

        // No need to set createdSegmentId since we're verifying deletion

        // Verify it's actually deleted by trying to get it (should throw ApiException)
        $this->expectException(ApiException::class);
        $this->laposta->segmentApi()->get(self::$listIdForTests, $segmentIdToDelete);
    }

    public function testGetAllSegments(): void
    {
        // Create a segment to ensure the list is not empty for this test
        $segmentName = 'GetAll Test Segment ' . $this->generateRandomString();
        $createData = [
            'name' => $segmentName,
            'definition' => $this->createDefinition(),
        ];
        $createdSegment = $this->laposta->segmentApi()->create(self::$listIdForTests, $createData);
        $this->createdSegmentId = $createdSegment['segment']['segment_id']; // Mark for cleanup

        $response = $this->laposta->segmentApi()->all(self::$listIdForTests);

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $found = false;
        foreach ($response['data'] as $segmentEntry) {
            if ($segmentEntry['segment']['segment_id'] === $this->createdSegmentId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created segment not found in all segments response.');
    }
}
