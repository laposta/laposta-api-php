<?php
require_once('../../../lib/Laposta.php');
Laposta::setApiKey('JdMtbsMq2jqJdQZD9AHC');
Laposta::setHttpsDisableVerifyPeer(true);

// initialize campaign object
$campaign = new Laposta_Campaign();

try {
	// update campaign content info, use campaign_id as argument
	// $result will contain een array with the response from the server
	$result = $campaign->update('pbrqulw2tc', array(
		'import_url' => 'http://google.com'
	), 'content');
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
