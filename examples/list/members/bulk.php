<?php
require_once('../../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");
Laposta::setHttpsDisableVerifyPeer(true);

// initialize list with list_id
$list = new Laposta_List();

$data = [
	'mode' => Laposta_Resource::BULK_MODE_ADD_AND_EDIT, // any of options Laposta_Resource::BULK_MODE_*
	'members' => [
		[
			/*
			 * We are providing the member_id for this member, this forces an update.
			 * If the member does not exist you will receive an error for this member.
			 */
			'member_id' => 'existing_member@example.com', // member_id requires either the id or the email of an existing member
			'email' => 'new_email@example.com', // the email is being updated
		],
		[
			'email' => 'member2@example.com', // if this member exists, it will be updated, otherwise it will be added
			// a custom field is mandatory, only if the member does not exist already and the field a required field
			'custom_fields' => [
				'my_custom_field1' => 'My custom Value 1',
				'my_custom_field2' => 'My custom Value 2',
			]
		],
	],
];

try {
	// $result will contain een array with the response from the server
	$result = $list->bulk('BaImMu3JZA', $data, 'members');
	print '<pre>';print_r($result);print '</pre>';

} catch (Exception $e) {

	// you can use the information in $e to react to the exception
	print '<pre>';print_r($e);print '</pre>';
}