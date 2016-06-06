<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey('JdMtbsMq2jqJdQZD9AHC');

// Send existing campaign.

// new campaign object
$campaign = new Laposta_Campaign();

try {

	// and send
	$result = $campaign->update('1234567890', array(), 'action', 'send');

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
