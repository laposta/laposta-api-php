<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Api;

use LapostaApi\Api\FieldApi;

class FieldApiTest extends BaseTestCase
{
    protected FieldApi $fieldApi;
    protected string $listId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listId = 'list123';
        $this->fieldApi = new FieldApi($this->laposta);
    }

    public function testGetField(): void
    {
        $fieldId = 'field123';
        $responseData = [
            'id' => $fieldId,
            'name' => 'Test Field',
            'tag' => 'test_field',
            'datatype' => 'text',
        ];

        $this->executeApiTest(
            fn() => $this->fieldApi->get($this->listId, $fieldId),
            200,
            $responseData,
            'GET',
            "/field/$fieldId",
            null,
            $responseData,
        );

        // Verify that list_id was included in the query parameters
        $request = $this->history[0]['request'];
        $this->assertEquals("list_id={$this->listId}", $request->getUri()->getQuery());
    }

    public function testCreateField(): void
    {
        $fieldData = [
            'name' => 'New Field',
            'tag' => 'new_field',
            'datatype' => 'text',
            'required' => true,
        ];

        $responseData = [
            'id' => 'field456',
            'name' => 'New Field',
            'tag' => 'new_field',
            'datatype' => 'text',
            'required' => true,
        ];

        // In the request, list_id should be added to the field data
        $expectedRequestData = $fieldData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->fieldApi->create($this->listId, $fieldData),
            201,
            $responseData,
            'POST',
            '/field',
            $expectedRequestData,
            $responseData,
        );
    }

    public function testUpdateField(): void
    {
        $fieldId = 'field123';
        $fieldData = [
            'name' => 'Updated Field',
            'required' => false,
        ];

        $responseData = [
            'id' => $fieldId,
            'name' => 'Updated Field',
            'tag' => 'test_field',
            'datatype' => 'text',
            'required' => false,
        ];

        // In the request, list_id should be added to the field data
        $expectedRequestData = $fieldData;
        $expectedRequestData['list_id'] = $this->listId;

        $this->executeApiTest(
            fn() => $this->fieldApi->update($this->listId, $fieldId, $fieldData),
            200,
            $responseData,
            'POST',
            "/field/$fieldId",
            $expectedRequestData,
            $responseData,
        );
    }

    public function testDeleteField(): void
    {
        $fieldId = 'field123';
        $responseData = ['success' => true];

        $this->executeApiTest(
            fn() => $this->fieldApi->delete($this->listId, $fieldId),
            200,
            $responseData,
            'DELETE',
            "/field/$fieldId",
            null,
            $responseData,
        );

        // Verify that list_id was included in the query parameters
        $request = $this->history[0]['request'];
        $this->assertEquals("list_id={$this->listId}", $request->getUri()->getQuery());
    }

    public function testGetAllFields(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => 'field123',
                    'name' => 'Field 1',
                    'tag' => 'field_1',
                    'datatype' => 'text',
                ],
                [
                    'id' => 'field456',
                    'name' => 'Field 2',
                    'tag' => 'field_2',
                    'datatype' => 'select',
                ],
            ],
        ];

        $result = $this->executeApiTest(
            fn() => $this->fieldApi->all($this->listId),
            200,
            $responseData,
            'GET',
            '/field',
            null,
            $responseData,
        );

        // Additional verification for this test
        $this->assertCount(2, $result['data']);

        // Verify that list_id was included in the query parameters
        $request = $this->history[0]['request'];
        $this->assertEquals("list_id={$this->listId}", $request->getUri()->getQuery());
    }
}
