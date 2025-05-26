<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$fieldId = getenv('LP_EX_FIELD_ID');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set.\n";
    exit(1);
}

if (!$fieldId) {
    echo "Error: LP_EX_FIELD_ID environment variable is not set. "
        . "Please set it to the ID of a field you wish to update.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);

// Update field
$fieldApi = $laposta->fieldApi();
$updateData = [
    'name' => 'Updated Custom Field Name - ' . date('Y-m-d H:i:s'),
];
try {
    $result = $fieldApi->update($listId, $fieldId, $updateData);
    echo "Field with ID '$fieldId' updated successfully:\n";
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
