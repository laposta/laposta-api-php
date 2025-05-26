<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$memberId = getenv('LP_EX_MEMBER_ID');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set.\n";
    exit(1);
}

if (!$memberId) {
    echo "Error: LP_EX_MEMBER_ID environment variable is not set. This is required to identify the member to update.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);

// Update member
$memberApi = $laposta->memberApi();
$updateData = [
    'ip' => '123.123.123.255', // Example updated IP
    'custom_fields' => [
        'firstname' => 'Jane',
        'lastname' => 'Doette', // Assuming these custom fields exist
    ],
];
try {
    $result = $memberApi->update($listId, $memberId, $updateData);
    echo "Member with ID '$memberId' in list '$listId' updated successfully:\n";
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
