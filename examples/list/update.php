<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set. "
        . "Please set it to the ID of a list you wish to update.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$listApi = $laposta->listApi();

// Update list
$updateData = [
    'name' => 'Updated List Name - ' . date('Y-m-d H:i:s'),
    'remarks' => 'These are updated remarks for the list.',
];
try {
    $result = $listApi->update($listId, $updateData);
    echo "List with ID '$listId' updated successfully:\n";
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
