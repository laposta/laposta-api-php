<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Integration\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Tests\Integration\BaseIntegrationTestCase;

class FieldApiIntegrationTest extends BaseIntegrationTestCase
{
    private string $createdFieldId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setListIdForTests('field');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::cleanupListForTests();
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdFieldId) && !empty(self::$listIdForTests)) {
            try {
                $this->laposta->fieldApi()->delete(self::$listIdForTests, $this->createdFieldId);
            } catch (ApiException $e) {
                fwrite(STDERR, "Cleanup error (field): " . $e->getMessage() . "\n");
            }
        }
        parent::tearDown();
    }

    private function createFieldData(string $namePrefix = 'field', array $overrides = []): array
    {
        $defaultData = [
            'name' => $namePrefix . '_' . strtolower($this->generateRandomString(8)),
            'datatype' => 'text',
            'required' => false,
            'in_form' => true,
            'in_list' => true,
        ];

        return array_merge($defaultData, $overrides);
    }

    public function testCreateField(): void
    {
        $fieldData = $this->createFieldData('custom_field');

        $response = $this->laposta->fieldApi()->create(self::$listIdForTests, $fieldData);

        $this->assertArrayHasKey('field', $response);
        $fieldResponse = $response['field'];
        $this->assertArrayHasKey('field_id', $fieldResponse);
        $this->assertEquals($fieldData['name'], $fieldResponse['name']);
        $this->assertEquals($fieldData['required'], $fieldResponse['required']);
        $this->assertEquals($fieldData['in_form'], $fieldResponse['in_form']);
        $this->assertEquals($fieldData['in_list'], $fieldResponse['in_list']);
        $this->createdFieldId = $fieldResponse['field_id'];
    }

    public function testGetField(): void
    {
        $fieldData = $this->createFieldData('get_field');

        $createdField = $this->laposta->fieldApi()->create(self::$listIdForTests, $fieldData);
        $this->createdFieldId = $createdField['field']['field_id'];

        $response = $this->laposta->fieldApi()->get(self::$listIdForTests, $this->createdFieldId);

        $this->assertArrayHasKey('field', $response);
        $fieldResponse = $response['field'];
        $this->assertEquals($this->createdFieldId, $fieldResponse['field_id']);
    }

    public function testUpdateField(): void
    {
        $fieldData = $this->createFieldData('update_orig_field');
        $createdField = $this->laposta->fieldApi()->create(self::$listIdForTests, $fieldData);
        $this->createdFieldId = $createdField['field']['field_id'];

        $updatedName = 'Updated - ' . $this->generateRandomString(4);
        $updateData = [
            'name' => $updatedName,
        ];

        $response = $this->laposta->fieldApi()->update(self::$listIdForTests, $this->createdFieldId, $updateData);

        $this->assertArrayHasKey('field', $response);
        $fieldResponse = $response['field'];
        $this->assertEquals($this->createdFieldId, $fieldResponse['field_id']);
        $this->assertEquals($updatedName, $fieldResponse['name']);
    }

    public function testDeleteField(): void
    {
        $fieldData = $this->createFieldData('delete_field');
        $createdField = $this->laposta->fieldApi()->create(self::$listIdForTests, $fieldData);
        $fieldIdToDelete = $createdField['field']['field_id'];

        $response = $this->laposta->fieldApi()->delete(self::$listIdForTests, $fieldIdToDelete);

        $this->assertArrayHasKey('field', $response);
        $fieldResponse = $response['field'];
        $this->assertArrayHasKey('state', $fieldResponse);
        $this->assertEquals('deleted', $fieldResponse['state']);

        // Verify it's actually deleted
        $this->expectException(ApiException::class);
        $this->laposta->fieldApi()->get(self::$listIdForTests, $fieldIdToDelete);
    }

    public function testGetAllFields(): void
    {
        $fieldData = $this->createFieldData('list_all_field');
        $createdFieldResponse = $this->laposta->fieldApi()->create(self::$listIdForTests, $fieldData);
        $createdFieldIdForThisTest = $createdFieldResponse['field']['field_id'];

        $response = $this->laposta->fieldApi()->all(self::$listIdForTests);

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $found = false;
        foreach ($response['data'] as $fieldEntry) {
            $this->assertArrayHasKey('field', $fieldEntry);
            if ($fieldEntry['field']['field_id'] === $createdFieldIdForThisTest) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created field not found in all fields response.');

        try {
            $this->laposta->fieldApi()->delete(self::$listIdForTests, $createdFieldIdForThisTest);
        } catch (ApiException $e) {
            fwrite(STDERR, "Cleanup error (campaign): " . $e->getMessage() . "\n");
        }
    }

    public function testCreateFieldWithSelectOptions(): void
    {
        $fieldData = $this->createFieldData('select_field', [
            'datatype' => 'select_single',
            'datatype_display' => 'select',
            'options' => ['Optie 1', 'Optie 2', 'Optie 3'],
        ]);

        $response = $this->laposta->fieldApi()->create(self::$listIdForTests, $fieldData);

        $this->assertArrayHasKey('field', $response);
        $fieldResponse = $response['field'];
        $this->assertArrayHasKey('field_id', $fieldResponse);
        $this->assertEquals('select_single', $fieldResponse['datatype']);
        $this->assertArrayHasKey('options', $fieldResponse);
        $this->assertArrayHasKey('options_full', $fieldResponse);
        $this->createdFieldId = $fieldResponse['field_id'];
    }
}
