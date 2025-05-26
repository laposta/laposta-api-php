<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;

class SegmentApi extends BaseApi
{
    /**
     * Get a single segment by ID.
     *
     * @param string $listId The ID of the list.
     * @param string $segmentId The ID of the segment to retrieve.
     *
     * @return array The segment data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function get(string $listId, string $segmentId): array
    {
        return $this->sendRequest(
            'GET',
            [$segmentId],
            ['list_id' => $listId],
        );
    }

    /**
     * Create a new segment.
     *
     * @param string $listId The ID of the list.
     * @param array $data The data for the new segment.
     *
     * @return array The created segment data.
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
     * Update an existing segment.
     *
     * @param string $listId The ID of the list.
     * @param string $segmentId The ID of the segment to update.
     * @param array $data The data to update the segment with.
     *
     * @return array The updated segment data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function update(string $listId, string $segmentId, array $data): array
    {
        $data['list_id'] = $listId;

        return $this->sendRequest(
            'POST',
            [$segmentId],
            body: $data,
        );
    }

    /**
     * Delete a segment.
     *
     * @param string $listId The ID of the list.
     * @param string $segmentId The ID of the segment to delete.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function delete(string $listId, string $segmentId): array
    {
        return $this->sendRequest(
            'DELETE',
            [$segmentId],
            ['list_id' => $listId],
        );
    }

    /**
     * Get all segments for the given list.
     *
     * @param string $listId The ID of the list.
     *
     * @return array All segments data.
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
