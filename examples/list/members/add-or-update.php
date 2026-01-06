<?php

/**
 * @deprecated This example uses the legacy 'mode' payload. See sync.php for the current actions-based example.
 */

require_once __DIR__ . '/../../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;
use LapostaApi\Type\BulkMode;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$listApi = $laposta->listApi();

// Bulk update
$data = [
    'mode' => BulkMode::ADD_AND_EDIT, // any of options BulkMode::*
    'members' => [
        [
            /*
             * We are providing the member_id for this member, this forces an update.
             * If the member does not exist you will receive an error for this member.
             */
            // member_id requires either the id or the email of an existing member
            'member_id' => 'existing_member@example.com',
            // the email is being updated
            'email' => 'new_email@example.com',
        ],
        [
            // if this member exists, it will be updated, otherwise it will be added
            'email' => 'member2@example.com',
            // a custom field is mandatory when the member does not exist already and the field a required field
            'custom_fields' => [
                'my_custom_field1' => 'My custom Value 1',
                'my_custom_field2' => 'My custom Value 2',
            ]
        ],
        [
            // if this member exists, it will be updated, otherwise it will be added
            'email' => 'member3@example.com',
            // a custom field is mandatory when the member does not exist already and the field a required field
            'custom_fields' => [
                'my_custom_field1' => 'My custom Value 1',
                'my_custom_field2' => 'My custom Value 2',
            ]
        ],
    ],
];
try {
    $result = $listApi->addOrUpdateMembers($listId, $data);
    echo "List members bulk performed successfully:\n";
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
