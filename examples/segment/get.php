<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// initialize segment with list_id
$segment = new Laposta_Segment("BaImMu3JZA");

try {
	// get segment info, use segment_id or email as argument
	// $result will contain een array with the response from the server
	$result = $segment->get("zyzQJle5BC");
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}
?>
