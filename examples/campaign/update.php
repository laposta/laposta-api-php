<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$campaignId = getenv('LP_EX_CAMPAIGN_ID');

// Validate required variables
if (!$campaignId) {
    echo "Error: LP_EX_CAMPAIGN_ID environment variable is not set. "
        . "Please set it to the ID of a campaign you wish to update.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$campaignApi = $laposta->campaignApi();

// Update campaign
$updateData = [
    'name' => 'Updated Campaign Name - ' . date('Y-m-d H:i:s'),
    'subject' => 'Updated Subject Line' . date('Y-m-d H:i:s'),
    // For content updates, use campaign->updateContent() - see campaign/content/update.php
];
try {
    $result = $campaignApi->update($campaignId, $updateData);
    echo "Campaign with ID '$campaignId' updated successfully:\n";
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
