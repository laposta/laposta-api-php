<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize campaign object
$campaign = new Laposta_Campaign();

try {
	// delete campaign, use campaign_id as argument
	// $result will contain een array with the response from the server
	$result = $campaign->delete("mvqedad500");
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
