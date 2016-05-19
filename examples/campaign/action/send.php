<?php
require_once('../../../lib/Laposta.php');
Laposta::setApiKey('JdMtbsMq2jqJdQZD9AHC');
Laposta::setHttpsDisableVerifyPeer(true);

// initialize campaign object
$campaign = new Laposta_Campaign();

try {
	// instruct campaign to be sent right away, use campaign_id as argument
	$result = $campaign->update('pbrqulw2tc', array(), 'action', 'send');
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
