<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$webhookTargetUrl = getenv('LP_EX_WEBHOOK_TARGET_URL');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set. "
        . "This is required to create a webhook for a specific list.\n";
    exit(1);
}

if (!$webhookTargetUrl) {
    echo "Error: LP_EX_WEBHOOK_TARGET_URL environment variable is not set. "
        . "This is required to specify the webhook endpoint.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$webhookApi = $laposta->webhookApi();

// Create webhook
$webhookData = [
    'event' => 'subscribed',
    'url' => $webhookTargetUrl,
    'blocked' => false,
];
try {
    $result = $webhookApi->create($listId, $webhookData);
    echo "Webhook created successfully for list_id '$listId':\n";
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
