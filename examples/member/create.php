<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize member with list_id
$member = new Laposta_Member("BaImMu3JZA");

try {
	// create new member, insert info as argument
	// $result will contain een array with the response from the server
	$result = $member->create(array(
		'ip' => '198.51.100.0',
		'email' => 'maartje@example.net',
		'source_url' => 'http://example.com',
		'custom_fields' => array(
			'name' => 'Maartje de Vries',
			'dateofbirth' => '1973-05-10',
			'children' => 2,
			'prefs' => array('optionA', 'optionB')
			)
		)
	);

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
