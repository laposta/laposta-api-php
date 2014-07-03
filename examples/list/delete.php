<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize list with list_id
$list = new Laposta_List();

try {
	// (permanently) delete list, use list_id as argument
	// $result will contain een array with the response from the server
	$result = $list->delete("eVdOsH8Yxs");
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
