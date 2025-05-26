<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;

class FieldApi extends BaseApi
{
    /**
     * Get a single field by ID.
     *
     * @param string $listId The ID of the list.
     * @param string $fieldId The ID of the field to retrieve.
     *
     * @return array The field data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function get(string $listId, string $fieldId): array
    {
        return $this->sendRequest(
            'GET',
            [$fieldId],
            ['list_id' => $listId],
        );
    }

    /**
     * Create a new field.
     *
     * @param string $listId The ID of the list.
     * @param array $data The data for the new field.
     *
     * @return array The created field data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function create(string $listId, array $data): array
    {
        $data['list_id'] = $listId;

        return $this->sendRequest(
            'POST',
            body: $data,
        );
    }

    /**
     * Update an existing field.
     *
     * @param string $listId The ID of the list.
     * @param string $fieldId The ID of the field to update.
     * @param array $data The data to update the field with.
     *
     * @return array The updated field data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function update(string $listId, string $fieldId, array $data): array
    {
        $data['list_id'] = $listId;

        return $this->sendRequest(
            'POST',
            [$fieldId],
            body: $data,
        );
    }

    /**
     * Delete a field.
     *
     * @param string $listId The ID of the list.
     * @param string $fieldId The ID of the field to delete.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function delete(string $listId, string $fieldId): array
    {
        return $this->sendRequest(
            'DELETE',
            [$fieldId],
            ['list_id' => $listId],
        );
    }

    /**
     * Get all fields for the given list.
     *
     * @param string $listId The ID of the list.
     *
     * @return array All fields data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function all(string $listId): array
    {
        return $this->sendRequest(
            'GET',
            queryParams: ['list_id' => $listId],
        );
    }
}
