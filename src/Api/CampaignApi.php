<?php

declare(strict_types=1);

namespace LapostaApi\Api;

use DateTimeInterface;
use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Query\CampaignHistoryQuery;
use LapostaApi\Query\CursorQueryParametersInterface;
use LapostaApi\Result\HistoryResult;

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
     * Get event history for a campaign.
     *
     * This performs one cursor-paginated API request. The returned
     * `HistoryResult` contains the current page and can fetch the next page
     * with `HistoryResult::next()`.
     *
     * Campaign history contains member events tied to this campaign, such as
     * sent, opened, clicked, bounced, and unsubscribe-related events. It does
     * not contain ordinary campaign create/update actions.
     * The API route is `/campaign/{campaign_id}/history`.
     *
     * @param string $campaignId The ID of the campaign.
     * @param CampaignHistoryQuery|null $query Optional history filters, ordering, and pagination.
     *
     * @return HistoryResult The campaign history result.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function history(string $campaignId, ?CampaignHistoryQuery $query = null): HistoryResult
    {
        $query ??= CampaignHistoryQuery::create();

        $response = $this->sendRequest(
            'GET',
            [$campaignId, 'history'],
            queryParams: $query->toQueryParams(),
        );

        return new HistoryResult(
            $response,
            $query,
            function (CursorQueryParametersInterface $nextQuery) use ($campaignId): HistoryResult {
                if (!$nextQuery instanceof CampaignHistoryQuery) {
                    throw new \RuntimeException('Expected a campaign history query.');
                }

                return $this->history($campaignId, $nextQuery);
            },
        );
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
     * Schedule campaign delivery.
     *
     * String values are sent unchanged. DateTimeInterface values are formatted
     * with an explicit timezone offset before being sent to the API.
     *
     * @param string $campaignId The ID of the campaign.
     *
     * @param string|DateTimeInterface $deliveryRequested The time and date of sending. String values are passed
     *                  through unchanged and may use YYYY-MM-DD HH:MM:SS in the account timezone or an
     *                  explicit-offset ISO/RFC3339 datetime. DateTimeInterface values are formatted with their
     *                  timezone offset.
     *
     * @return array The campaign data.
     * @throws ApiException
     * @throws ClientException
     * @throws \JsonException
     */
    public function schedule(string $campaignId, string|DateTimeInterface $deliveryRequested): array
    {
        if ($deliveryRequested instanceof DateTimeInterface) {
            $deliveryRequested = $deliveryRequested->format(DateTimeInterface::ATOM);
        }

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
