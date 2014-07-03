<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize webhook with list_id
$webhook = new Laposta_Webhook("BaImMu3JZA");

try {
	// create new webhook, insert info as argument
	// $result will contain een array with the response from the server
	$result = $webhook->create(array(
		'event' => 'modified',
		'url' => 'http://example.com/webhook.pl',
		'blocked' => 'false'
		)
	);

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
