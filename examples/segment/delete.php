<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$listId = getenv('LP_EX_LIST_ID');
$segmentIdToDelete = getenv('LP_EX_SEGMENT_ID_TO_DELETE');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set. This is required to identify the list.\n";
    exit(1);
}

if (!$segmentIdToDelete) {
    echo "Error: LP_EX_SEGMENT_ID_TO_DELETE environment variable is not set. "
        . "Please set it to the ID of a segment you wish to delete.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);

// Delete segment
$segmentApi = $laposta->segmentApi();
try {
    $result = $segmentApi->delete($listId, $segmentIdToDelete);
    echo "Segment with ID '$segmentIdToDelete' in list '$listId' deleted successfully:\n";
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
