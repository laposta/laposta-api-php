<?php
require_once('../setup.php');

// initialize list
$list = new Laposta\List_();

try {
	// get all members from this list
	// $result will contain een array with the response from the server
	$result = $list->all();
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
