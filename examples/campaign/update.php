<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey('JdMtbsMq2jqJdQZD9AHC');

// new campaign object
$campaign = new Laposta_Campaign();

try {
	// update campaign, use id as first argument
	// $result will contain een array with the response from the server
	$result = $campaign->update('pbrqulw2tc', array(
		'subject' => 'This is the MODIFIED subject'
	));

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
