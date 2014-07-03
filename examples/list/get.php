<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");
Laposta::setHttpsDisableVerifyPeer(true);

// initialize list with list_id
$list = new Laposta_List();

try {
	// get list info, use list_id as argument
	// $result will contain een array with the response from the server
	$result = $list->get("BaImMu3JZA");
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
