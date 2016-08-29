<?php
require_once('../setup.php');

// new campaign object
$campaign = new Laposta\Campaign();

try {
	// create new campaign, insert info as argument
	// $result will contain een array with the response from the server
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

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
