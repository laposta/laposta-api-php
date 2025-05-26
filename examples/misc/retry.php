<?php

/**
 * Example: Retry mechanism for creating a member using the Laposta API client.
 *
 * This example demonstrates how to implement a simple retry loop around an API call
 * when using the Laposta client with its default HTTP implementation.
 *
 * It retries the request up to 3 times when a temporary error occurs (e.g. 429 Too Many Requests,
 * 500 Internal Server Error), using a linear backoff strategy.
 */

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
        . "This is required to create a member in a specific list.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);

// Create member in a retry cycle
$memberApi = $laposta->memberApi();
$memberData = [
    'email' => 'example+' . time() . '@example.com',
    'ip' => '123.123.123.123',
];
$maxRetries = 3;
$attempt = 0;
do {
    $attempt++;

    try {
        $result = $memberApi->create($listId, $memberData);
        echo "Member created successfully in list_id '$listId' in attempt $attempt:\n";
        print_r($result);
        break;
    } catch (ApiException $e) {
        $response = $e->getResponse();
        if (in_array($response->getStatusCode(), [429, 500, 502, 503], true) && $attempt <= $maxRetries) {
            $sleep = $attempt;  // linear backoff
            if ($response->getHeaderLine('Retry-After')) {
                $sleep = (int)$response->getHeaderLine('Retry-After');
            }
            $sleep = max(1, $sleep);
            echo "ApiException: " . $e->getMessage() . "\n";
            echo "Retrying (" . ($attempt) . ") after {$response->getStatusCode()}, sleeping for $sleep seconds...\n";
            sleep($sleep);
        } else {
            echo "ApiException: " . $e->getMessage() . "\n";
            echo "Response body: " . $e->getResponseBody() . "\n";
            break;
        }
    } catch (ClientException $e) {
        echo "ClientException: " . $e->getMessage() . "\n";
        echo "Response body: " . $e->getResponseBody() . "\n";
        break;
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        break;
    }
} while ($attempt <= $maxRetries);
