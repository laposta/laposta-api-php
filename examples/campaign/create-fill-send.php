<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');
$approvedSenderAddress = getenv('LP_EX_APPROVED_SENDER_ADDRESS');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set.\n";
    exit(1);
}

if (!$approvedSenderAddress) {
    echo "Error: LP_EX_APPROVED_SENDER_ADDRESS environment variable is not set.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$campaignApi = $laposta->campaignApi();

// Create, fill, and send campaign
$campaignId = null;
try {
    // 1. Create a new campaign
    $campaignData = [
        'type' => 'regular',
        'name' => 'My Create-Fill-Send Campaign ' . date('Y-m-d H:i:s'),
        'list_ids' => [$listId],
        'subject' => 'Subject for Create-Fill-Send',
        'from' => [
            'name' => 'CFS Sender Name',
            'email' => $approvedSenderAddress,
        ],
    ];
    $createResult = $campaignApi->create($campaignData);
    echo "Campaign created successfully:\n";
    print_r($createResult);
    echo "\n";

    if (!isset($createResult['campaign']['campaign_id'])) {
        echo "Error: Could not retrieve campaign_id from creation response.\n";
        exit(1);
    }
    $campaignId = $createResult['campaign']['campaign_id'];
    echo "New campaign_id: " . $campaignId . "\n";

    // 2. Fill (Update) the campaign content
    $contentData = [
        'html' => '<html lang="en"><head><title>CFS Title</title></head>' .
            '<body><h1>Hello!</h1>' .
            '<p>This is a test campaign created, filled, and sent via the API.</p>' .
            '</body></html>',
    ];
    $updateContentResult = $campaignApi->updateContent($campaignId, $contentData);
    echo "Campaign content updated successfully:\n";
    print_r($updateContentResult);
    echo "\n";

    // 3. Send the campaign (immediately)
    // Note: Sending a campaign is a critical action.
    $confirm = readline(
        "Are you ENTIRELY sure you want to send the newly created campaign to list '$listId'? "
        . "Y / N: "
    );
    if (strcasecmp($confirm, 'Y') !== 0) {
        echo "Aborted.\n";
        exit(1);
    }

    $sendResult = $campaignApi->send($campaignId);
    echo "Campaign scheduled for sending successfully:\n";
    print_r($sendResult);
} catch (ApiException $e) {
    echo "ApiException: " . $e->getMessage() . "\n";
    echo "Request: {$e->getRequest()->getMethod()} {$e->getRequest()->getUri()}" . "\n";
    echo "Response body: " . $e->getResponseBody() . "\n";
} catch (ClientException $e) {
    echo "ClientException: " . $e->getMessage() . "\n";
    echo "Response body: " . $e->getResponseBody() . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
