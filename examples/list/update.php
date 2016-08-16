<?php
require_once('../setup.php');

// initialize list with list_id
$list = new Laposta\List_();

try {
	// update list, insert info as argument
	// $result will contain een array with the response from the server
	$result = $list->update('FKjs6srKdf', array(
                'name' => 'Klanten'
		)
	);
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';

}
?>
