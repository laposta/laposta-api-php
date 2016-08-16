<?php
require_once('../setup.php');

// initialize campaign
$campaign = new Laposta\Campaign();

try {
	// get all campaign from account
	// $result will contain een array with the response from the server
	$result = $campaign->all();
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
