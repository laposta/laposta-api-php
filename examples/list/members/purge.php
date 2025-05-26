<?php

require_once __DIR__ . '/../../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID_TO_PURGE_MEMBERS_FROM');
;

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID_TO_PURGE_MEMBERS_FROM environment variable is not set.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$listApi = $laposta->listApi();

// Purge members from list
try {
    $result = $listApi->purgeMembers($listId);
    echo "Purge members performed successfully for list_id '$listId':\n";
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
