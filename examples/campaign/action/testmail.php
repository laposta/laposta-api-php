<?php

require_once __DIR__ . '/../../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$campaignId = getenv('LP_EX_CAMPAIGN_ID_TO_SEND');
$testEmail = getenv('LP_EX_CAMPAIGN_TEST_EMAIL');

// Validate required variables
if (!$campaignId) {
    echo "Error: LP_EX_CAMPAIGN_ID_TO_SEND environment variable is not set. "
        . "Please set it to the ID of a campaign you wish to send.\n";
    exit(1);
}

if (!$testEmail) {
    echo "Error: LP_EX_CAMPAIGN_TEST_EMAIL environment variable is not set.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$campaignApi = $laposta->campaignApi();

// Send test email for the campaign
try {
    $result = $campaignApi->sendTestMail($campaignId, $testEmail);
    echo "Test Email sent to Campaign with ID '$campaignId to $testEmail:\n";
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
