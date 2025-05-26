<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Type\ContentType;

class ListApi extends BaseApi
{
    /**
     * Get a single list by ID.
     *
     * @param string $listId The ID of the list to retrieve.
     *
     * @return array The list data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function get(string $listId): array
    {
        return $this->sendRequest(
            'GET',
            [$listId],
        );
    }

    /**
     * Create a new list.
     *
     * @param array $data The data for the new list.
     *
     * @return array The created list data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function create(array $data): array
    {
        return $this->sendRequest(
            'POST',
            body: $data,
        );
    }

    /**
     * Update an existing list.
     *
     * @param string $listId The ID of the list to update.
     * @param array $data The data to update the list with.
     *
     * @return array The updated list data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function update(string $listId, array $data): array
    {
        return $this->sendRequest(
            'POST',
            [$listId],
            body: $data,
        );
    }

    /**
     * Delete a list.
     *
     * @param string $listId The ID of the list to delete.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function delete(string $listId): array
    {
        return $this->sendRequest(
            'DELETE',
            [$listId],
        );
    }

    /**
     * Get all lists.
     *
     * @return array All lists data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function all(): array
    {
        return $this->sendRequest('GET');
    }

    /**
     * Perform a purge members operation on a list.
     *
     * @param string $listId The ID of the list.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function purgeMembers(string $listId): array
    {
        return $this->sendRequest(
            'DELETE',
            [$listId, 'members'],
        );
    }

    /**
     * Perform a bulk operation to add and/or update members in a list.
     *
     * The $data array must include a 'mode' key with value 'add', 'edit', or 'add_and_edit',
     * and a 'members' key containing an array of member objects.
     *
     * @param string $listId The ID of the list.
     * @param array $data The data for the bulk operation.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function addOrUpdateMembers(string $listId, array $data): array
    {
        return $this->sendRequest(
            'POST',
            [$listId, 'members'],
            body: $data,
            contentType: ContentType::JSON,
        );
    }
}
