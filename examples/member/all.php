<?php
require_once('../setup.php');

// initialize member with list_id
$member = new Laposta\Member("BaImMu3JZA");

try {
	// get all members from this list
	// $result will contain een array with the response from the server
	$result = $member->all();
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
