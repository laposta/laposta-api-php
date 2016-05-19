<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey('JdMtbsMq2jqJdQZD9AHC');

// Create a new campaign, fill it, and send it.
// Left out most error-checking for brevity.

// new campaign object
$campaign = new Laposta_Campaign();

try {
	// first create new campaign
	$result = $campaign->create(array(
		'type' => 'regular',
		'name' => 'Test API ' . date('d-m-Y H:i:s'),
		'subject' => 'This is the subject',
		'from' => array(
			'name' => 'Max de Vries',
			'email' => 'max@example.net'
		),
		'reply_to' => 'reply@example.net',
		'list_ids' => array(
			'nnhnkrytua', 'srbhotdwob'
		),
		'stats' => array(
			'ga' => 'true'
		)
	));

	// get campaign_id from $result
	$campaign_id = $result['campaign']['campaign_id'];

	// import url
	$campaign->update($campaign_id, array(
		'import_url' => 'http://google.com'
	), 'content');

	// en verstuur direct
	$campaign->update($campaign_id, array(), 'action', 'send');

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
