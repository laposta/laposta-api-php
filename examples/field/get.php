<?php
require_once('../setup.php');

// initialize field with list_id
$field = new Laposta\Field("BaImMu3JZA");

try {
	// get field info, use field_id or email as argument
	// $result will contain een array with the response from the server
	$result = $field->get("iPcyYaTCkG");
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
