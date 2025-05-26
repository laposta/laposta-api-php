<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listIdToDelete = getenv('LP_EX_LIST_ID_TO_DELETE');

// Validate required variables
if (!$listIdToDelete) {
    echo "Error: LP_EX_LIST_ID_TO_DELETE environment variable is not set. "
        . "Please set it to the ID of a list you wish to delete.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$listApi = $laposta->listApi();

// Delete list
try {
    $result = $listApi->delete($listIdToDelete);
    echo "List with ID '$listIdToDelete' deleted successfully:\n";
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
