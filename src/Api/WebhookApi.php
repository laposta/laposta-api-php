<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;

class WebhookApi extends BaseApi
{
    /**
     * Retrieve a single webhook by ID.
     *
     * @param string $listId The ID of the list to get the webhook from.
     * @param string $webhookId The ID of the webhook to retrieve.
     *
     * @return array The webhook data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function get(string $listId, string $webhookId): array
    {
        return $this->sendRequest(
            'GET',
            [$webhookId],
            queryParams: ['list_id' => $listId],
        );
    }

    /**
     * Create a new webhook.
     *
     * @param string $listId The ID of the list to create the webhook for.
     * @param array $data The data for the new webhook.
     *
     * @return array The created webhook data.
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
     * Update an existing webhook.
     *
     * @param string $listId The ID of the list containing the webhook.
     * @param string $webhookId The ID of the webhook to update.
     * @param array $data The data to update the webhook with.
     *
     * @return array The updated webhook data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function update(string $listId, string $webhookId, array $data): array
    {
        $data['list_id'] = $listId;

        return $this->sendRequest(
            'POST',
            [$webhookId],
            body: $data,
        );
    }

    /**
     * Delete a webhook.
     *
     * @param string $listId The ID of the list containing the webhook.
     * @param string $webhookId The ID of the webhook to delete.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function delete(string $listId, string $webhookId): array
    {
        return $this->sendRequest(
            'DELETE',
            [$webhookId],
            queryParams: ['list_id' => $listId],
        );
    }

    /**
     * Retrieve all webhooks for the specified list.
     *
     * @param string $listId The ID of the list to get webhooks from.
     *
     * @return array All webhooks data.
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
