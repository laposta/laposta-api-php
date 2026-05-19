<?php

require_once __DIR__ . '/../bootstrap.php';

use LapostaApi\Exception\ApiException;
use LapostaApi\Exception\ClientException;
use LapostaApi\Laposta;
use LapostaApi\Query\CampaignHistoryQuery;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$campaignId = getenv('LP_EX_CAMPAIGN_ID');
// Bare date strings use the account timezone. Include an offset, for example
// 2026-05-19T10:00:00+02:00, to filter by an exact moment.
// DateTimeInterface values passed to the query methods are always sent with an offset, including +00:00 for UTC.
$eventDateFrom = getenv('LP_EX_HISTORY_EVENT_DATE_FROM') ?: null;
$eventDateTo = getenv('LP_EX_HISTORY_EVENT_DATE_TO') ?: null;
$limit = getenv('LP_EX_HISTORY_LIMIT') ?: null;
$order = getenv('LP_EX_HISTORY_ORDER') ?: null;
$maxPages = (int)(getenv('LP_EX_HISTORY_MAX_PAGES') ?: 3);
if ($maxPages < 1) {
    $maxPages = 1;
}

// Validate required variables
if (!$campaignId) {
    echo "Error: LP_EX_CAMPAIGN_ID environment variable is not set.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$query = CampaignHistoryQuery::create()
    ->withEventDateFrom($eventDateFrom)
    ->withEventDateTo($eventDateTo)
    ->withLimit($limit === null ? null : (int)$limit)
    ->withEventDateOrder($order);

// Get campaign history
$campaignApi = $laposta->campaignApi();
try {
    $historyResult = $campaignApi->history($campaignId, $query);
    echo "History for campaign '$campaignId' retrieved successfully:\n";

    $page = 1;
    while ($historyResult !== null) {
        echo "\nPage $page of max $maxPages\n";

        echo "\nFilters:\n";
        print_r($historyResult->filters());

        echo "\nOrder:\n";
        print_r($historyResult->order());

        echo "\nNext cursor:\n";
        var_export($historyResult->nextCursor());
        echo "\n";

        $events = $historyResult->events();

        echo "\nEvent count:\n";
        echo count($events) . "\n";

        foreach ($events as $event) {
            echo "\nFirst event data:\n";
            print_r($event);
            echo "\n";
            break; // limit output in this example
        }

        if (!$historyResult->hasMore()) {
            break;
        }

        if ($page >= $maxPages) {
            echo "\nMore history is available. Increase LP_EX_HISTORY_MAX_PAGES to fetch additional pages.\n";
            break;
        }

        $historyResult = $historyResult->next();
        $page++;
    }
} catch (ApiException $e) {
    echo "ApiException: " . $e->getMessage() . "\n";
    echo "Response body: " . $e->getResponseBody() . "\n";
} catch (ClientException $e) {
    echo "ClientException: " . $e->getMessage() . "\n";
    echo "Response body: " . $e->getResponseBody() . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
