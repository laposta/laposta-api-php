<?php
require_once('../setup.php');

// initialize webhook with list_id
$webhook = new Laposta\Webhook("BaImMu3JZA");

try {
	// get webhook info, use webhook_id or email as argument
	// $result will contain een array with the response from the server
	$result = $webhook->get("cW5ls8IVJl");
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
