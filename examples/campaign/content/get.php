<?php

require_once __DIR__ . '/../../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$campaignId = getenv('LP_EX_CAMPAIGN_ID');

// Validate required variables
if (!$campaignId) {
    echo "Error: LP_EX_CAMPAIGN_ID environment variable is not set.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$campaignApi = $laposta->campaignApi();

// Get campaign content
try {
    $result = $campaignApi->getContent($campaignId);
    echo "Campaign content retrieved successfully for campaign_id '$campaignId':\n";
    print_r($result);
} catch (ApiException $e) {
    echo "ApiException: " . $e->getMessage() . "\n";
    echo "Response body: " . $e->getResponseBody() . "\n";
} catch (ClientException $e) {
    echo "ClientException: " . $e->getMessage() . "\n";
    echo "Response body: " . $e->getResponseBody() . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
