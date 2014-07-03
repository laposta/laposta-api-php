<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize field with list_id
$field = new Laposta_Field("BaImMu3JZA");

try {
	// create new field, insert info as argument
	// $result will contain een array with the response from the server
	// Note: we need to put 'true' in quotes, because php translates true to 1 and false to [empty]
	$result = $field->create(array(
		'name' => 'Naam',
		'datatype' => 'text',
		'required' => 'true',
		'in_form' => 'true',
		'in_list' => 'true'
		)
	);

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
