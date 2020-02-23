<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey('JdMtbsMq2jqJdQZD9AHC');
Laposta::setHttpsDisableVerifyPeer(true);

// initialize report object
$report = new Laposta_Report();

try {
	// get campaign results info, use campaign_id as argument
	// $result will contain een array with the response from the server
	$result = $report->get('94wb6pucra');
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
