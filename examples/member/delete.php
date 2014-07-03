<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize member with list_id
$member = new Laposta_Member("BaImMu3JZA");

try {
	// (permanently) delete member, use member_id or email as argument
	// $result will contain een array with the response from the server
	$result = $member->delete("maartje@example.net");
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
