<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize list with list_id
$list = new Laposta_List();

try {
	// create new list, insert info as argument
	// $result will contain een array with the response from the server
	$result = $list->create(array(
		'name' => 'Testlijst',
		'remarks' => 'Een lijst om mee te testen',
		'subscribe_notification_email' => 'aanmeldingen@example.net',
		'unsubscribe_notification_email' => 'afmeldingen@example.net'
		)
	);

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
