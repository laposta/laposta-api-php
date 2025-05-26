<?php

// examples/config-example.php
// Copy this file to config.php in the same directory (examples/) and set the values

return [
    'LP_EX_API_KEY' => '',

    // Campaign examples
    'LP_EX_CAMPAIGN_ID' => '',
    'LP_EX_CAMPAIGN_ID_TO_DELETE' => '',
    'LP_EX_CAMPAIGN_ID_TO_SEND' => '',
    'LP_EX_CAMPAIGN_TEST_EMAIL' => '', // e.g. example@domain.com
    'LP_EX_APPROVED_SENDER_ADDRESS' => '',

    // List examples
    'LP_EX_LIST_ID' => '',
    'LP_EX_LIST_ID_TO_DELETE' => '',
    'LP_EX_LIST_ID_TO_PURGE_MEMBERS_FROM' => '', // List to purge members from

    // Field examples
    'LP_EX_FIELD_ID' => '',
    'LP_EX_FIELD_ID_TO_DELETE' => '',

    // Member examples
    'LP_EX_MEMBER_ID' => '', // Requires corresponding LIST_ID
    'LP_EX_MEMBER_ID_TO_DELETE' => '', // Requires corresponding LIST_ID

    // Segment examples
    'LP_EX_SEGMENT_ID' => '', // Requires corresponding LIST_ID
    'LP_EX_SEGMENT_ID_TO_DELETE' => '', // Requires corresponding LIST_ID

    // Webhook examples
    'LP_EX_WEBHOOK_TARGET_URL' => '', // Requires corresponding LIST_ID
    'LP_EX_WEBHOOK_ID' => '', // Requires corresponding LIST_ID
    'LP_EX_WEBHOOK_ID_TO_DELETE' => '', // Requires corresponding LIST_ID

    // Note: Ensure the IDs used (LP_EX_CAMPAIGN_ID, LP_EX_LIST_ID, etc.) correspond to actual entities in
    // your Laposta account for the examples to work correctly.
    // For deletion/update examples, use IDs of entities you are willing to modify or delete.
];
