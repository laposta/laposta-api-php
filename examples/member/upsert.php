<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey('TrbeCSZ6KRs4zwB27ZCg');

// initialize member with list_id
$member = new Laposta_Member("shb5vujyxj");

try {
	// upsert
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
			),
		'options' => array(
                        'upsert' => true
			)
		)
	);

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
