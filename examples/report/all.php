<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey('JdMtbsMq2jqJdQZD9AHC');

// initialize report object
$report = new Laposta_Report();

try {
	// get results from all campaigns from account
	// $result will contain een array with the response from the server
	$result = $report->all();
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
