<?php
require_once('../setup.php');

// initialize list with list_id
$list = new Laposta\List_();

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
