<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;

class CampaignApi extends BaseApi
{
    /**
     * Get campaign details.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @return array The campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function get(string $campaignId): array
    {
        return $this->sendRequest(
            'GET',
            [$campaignId],
        );
    }

    /**
     * Create a new campaign.
     *
     * @param array $data The data for the new campaign.
     *
     * @return array The created campaign data.
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
     * Update an existing campaign.
     *
     * @param string $campaignId The ID of the campaign.
     * @param array $data The data to update the campaign with.
     *
     * @return array The updated campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function update(string $campaignId, array $data): array
    {
        return $this->sendRequest(
            'POST',
            [$campaignId],
            body: $data,
        );
    }

    /**
     * Delete a campaign.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @return array Response data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function delete(string $campaignId): array
    {
        return $this->sendRequest(
            'DELETE',
            [$campaignId],
        );
    }

    /**
     * Get all campaigns.
     *
     * @return array The data of all campaigns.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function all(): array
    {
        return $this->sendRequest('GET');
    }

    /**
     * Get campaign content.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @return array The campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function getContent(string $campaignId): array
    {
        return $this->sendRequest(
            'GET',
            [$campaignId, 'content'],
        );
    }

    /**
     * Update (or add) campaign content.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @return array The campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function updateContent(string $campaignId, array $data): array
    {
        return $this->sendRequest(
            'POST',
            [$campaignId, 'content'],
            body: $data,
        );
    }

    /**
     * Send campaign content.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @return array The campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function send(string $campaignId): array
    {
        return $this->sendRequest(
            'POST',
            [$campaignId, 'action', 'send'],
        );
    }

    /**
     * Schedule campaign.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @param string $deliveryRequested The time and date of sending (format YYYY-MM-DD HH:MM:SS)
     *                  in the account's timezone.
     *
     * @return array The campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function schedule(string $campaignId, string $deliveryRequested): array
    {
        $data = [
            'delivery_requested' => $deliveryRequested,
        ];

        return $this->sendRequest(
            'POST',
            [$campaignId, 'action', 'schedule'],
            body: $data,
        );
    }

    /**
     * Test campaign by sending a test mail.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @param string $email The email address to which the test should be sent.
     *
     * @return array The campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function sendTestMail(string $campaignId, string $email): array
    {
        $data = [
            'email' => $email,
        ];

        return $this->sendRequest(
            'POST',
            [$campaignId, 'action', 'testmail'],
            body: $data,
        );
    }
}
