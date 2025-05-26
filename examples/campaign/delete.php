<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$campaignIdToDelete = getenv('LP_EX_CAMPAIGN_ID_TO_DELETE');

// Validate required variables
if (!$campaignIdToDelete) {
    echo "Error: LP_EX_CAMPAIGN_ID_TO_DELETE environment variable is not set. "
        . "Please set it to the ID of a campaign you wish to delete.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$campaignApi = $laposta->campaignApi();

// Delete campaign
try {
    $result = $campaignApi->delete($campaignIdToDelete);
    echo "Campaign with ID '$campaignIdToDelete' deleted successfully:\n";
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
