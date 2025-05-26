<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;

class MemberApi extends BaseApi
{
    /**
     * Get a single member by ID.
     *
     * @param string $listId The ID of the list containing the member.
     * @param string $memberId The ID of the member to retrieve.
     *
     * @return array The member data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function get(string $listId, string $memberId): array
    {
        return $this->sendRequest(
            'GET',
            [$memberId],
            queryParams: ['list_id' => $listId],
        );
    }

    /**
     * Create a new member.
     *
     * @param string $listId The ID of the list to create the member in.
     * @param array $data The data for the new member.
     *
     * @return array The created member data.
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
     * Update an existing member.
     *
     * @param string $listId The ID of the list containing the member.
     * @param string $memberId The ID of the member to update.
     * @param array $data The data to update the member with.
     *
     * @return array The updated member data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function update(string $listId, string $memberId, array $data): array
    {
        $data['list_id'] = $listId;

        return $this->sendRequest(
            'POST',
            [$memberId],
            body: $data,
        );
    }

    /**
     * Delete a member.
     *
     * @param string $listId The ID of the list containing the member.
     * @param string $memberId The ID of the member to delete.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function delete(string $listId, string $memberId): array
    {
        return $this->sendRequest(
            'DELETE',
            [$memberId],
            queryParams: ['list_id' => $listId],
        );
    }

    /**
     * Get all members.
     *
     * @param string $listId The ID of the list to get members from.
     *
     * @return array All members data.
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
