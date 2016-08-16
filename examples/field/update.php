<?php
require_once('../setup.php');

// initialize field with list_id
$field = new Laposta\Field("BaImMu3JZA");

/*
try {
	// update field, insert info as argument
	// $result will contain een array with the response from the server
	$result = $field->update('PbRsfv2sek', array(
		'required' => 'false'
		)
	);
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';

}
*/

// example with select_multiple
try {
	// update field, insert info as argument
	// $result will contain een array with the response from the server
	$result = $field->update('TOXHXMUGKi', array(
		'options_full' => array(
			'0' => 'Rood',
			'1' => 'Oranje',
			'2' => 'Groen'
		)
		)
	);
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';

}
?>
