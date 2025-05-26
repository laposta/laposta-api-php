<?php

require_once __DIR__ . '/../../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$campaignId = getenv('LP_EX_CAMPAIGN_ID_TO_SEND');

// Validate required variables
if (!$campaignId) {
    echo "Error: LP_EX_CAMPAIGN_ID_TO_SEND environment variable is not set. "
        . "Please set it to the ID of a campaign you wish to schedule.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$campaignApi = $laposta->campaignApi();

// Schedule campaign
$deliveryRequested = '2099-12-31 23:59:59';
try {
    $result = $campaignApi->schedule($campaignId, $deliveryRequested);
    echo "Campaign with ID '$campaignId' scheduled for sending at '$deliveryRequested'" . " successfully:\n";
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
