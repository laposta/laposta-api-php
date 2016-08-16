<?php
require_once('../setup.php');

// initialize field with list_id
$field = new Laposta\Field("srbhotdwob");

try {
	// create new field, insert info as argument
	// $result will contain een array with the response from the server
	// Note: we need to put 'true' in quotes, because php translates true to 1 and false to [empty]
	$result = $field->create(array(
		'name' => 'Kleur',
		'defaultvalue' => 'Groen',
		'datatype' => 'select_multiple',
		'options' => array('Rood', 'Groen', 'Blauw'),
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
