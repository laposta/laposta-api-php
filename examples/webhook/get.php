<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$webhookId = getenv('LP_EX_WEBHOOK_ID');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set. "
        . "This is required to identify the list associated with the webhook.\n";
    exit(1);
}

if (!$webhookId) {
    echo "Error: LP_EX_WEBHOOK_ID environment variable is not set. This is required to identify the webhook.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$webhookApi = $laposta->webhookApi();

// Get webhook details
try {
    $result = $webhookApi->get($listId, $webhookId);
    echo "Webhook with ID '$webhookId' for list_id '$listId' retrieved successfully:\n";
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
