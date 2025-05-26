<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$listId = getenv('LP_EX_LIST_ID');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set. This is required to identify the list.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);

// Upsert member
$memberApi = $laposta->memberApi($listId);
$memberData = [
    'email' => 'upsert_example@example.com',
    'ip' => '123.123.123.123', // Example IP, optional
    'custom_fields' => [
        'firstname' => 'Upsert',
        'lastname' => 'Example',
    ],
    'options' => [
        'upsert' => true, // This is the key setting for upsert functionality
        'subscribe_if_new' => true, // Default true
        'subscribe_if_existing' => true, // Set to true to resubscribe if unsubscribed
    ],
];
try {
    $result = $memberApi->create($listId, $memberData);
    echo "Member upserted successfully in list '$listId':\n";
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
