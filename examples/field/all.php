<?php
require_once('../setup.php');

// initialize field with list_id
$field = new Laposta\Field("BaImMu3JZA");

try {
	// get all fields from this list
	// $result will contain een array with the response from the server
	$result = $field->all();
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
