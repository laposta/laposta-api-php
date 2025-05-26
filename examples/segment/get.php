<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$segmentId = getenv('LP_EX_SEGMENT_ID');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set. This is required to identify the list.\n";
    exit(1);
}

if (!$segmentId) {
    echo "Error: LP_EX_SEGMENT_ID environment variable is not set. This is required to identify the segment.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);

// Get segment details
$segmentApi = $laposta->segmentApi();
try {
    $result = $segmentApi->get($listId, $segmentId);
    echo "Segment with ID '$segmentId' in list '$listId' retrieved successfully:\n";
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
