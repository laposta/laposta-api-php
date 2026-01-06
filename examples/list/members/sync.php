<?php

require_once __DIR__ . '/../../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;
use LapostaApi\Type\SyncAction;

$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');

if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set.\n";
    exit(1);
}

$laposta = new Laposta($apiKey);
$listApi = $laposta->listApi();

$data = [
    'actions' => [
        SyncAction::ADD,
        SyncAction::UPDATE,
//        SyncAction::UNSUBSCRIBE_EXCLUDED, // omit existing members to unsubscribe them during sync
    ],
    'members' => [
        [
            'email' => 'example-sync-add@example.com',
            'custom_fields' => [
                'first_name' => 'Sync',
                'last_name' => 'Add',
            ],
        ],
        [
            'email' => 'example-sync-update@example.com',
            'member_id' => 'existing_member@example.com',
        ],
    ],
];

try {
    $result = $listApi->syncMembers($listId, $data);
    echo "List members synced successfully:\n";
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
