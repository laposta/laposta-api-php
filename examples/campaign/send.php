<?php
require_once('../setup.php');

// Send existing campaign.

// new campaign object
$campaign = new Laposta\Campaign();

try {

	// and send
	$result = $campaign->update('1234567890', array(), 'action', 'send');

	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
