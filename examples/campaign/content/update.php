<?php
require_once('../../setup.php');

// initialize campaign object
$campaign = new Laposta\Campaign();

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
