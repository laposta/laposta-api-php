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
        . "This is required to create a segment in a specific list.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);

// Create segment
$segmentApi = $laposta->segmentApi();
$segmentData = [
    'name' => 'Subscription date',
    'definition' => json_encode([
        'blocks' => [
            [
                'rules' => [
                    [
                        'input' => [
                            'subject' => 'signup',
                            'comparator' => 'equal',
                            'multi' => '',
                            'text' => '2025-05-07'
                        ],
                        'type' => 'members'
                    ]
                ]
            ]
        ]
    ])
];
try {
    $result = $segmentApi->create($listId, $segmentData);
    echo "Segment created successfully in list_id '$listId':\n";
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
