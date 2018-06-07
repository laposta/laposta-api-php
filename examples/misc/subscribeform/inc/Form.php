<?php
class Form {

	private $list_id;
	private $fields;
	private $lang;
	private $values;
	private $error;

	public function __construct($vars) {

		$this->list_id = $vars['list_id'];
		$this->lang = $vars['lang'];

		// get fields
		$this->fields = $this->getFields();
	}

	public function render() {

		// we should have fields
		if (!$this->fields) {
			return 'The form cannot be shown. Check the API and list_id keys.';
		}

		$html = '';

		// walk through fields
		foreach($this->fields as $field) {

			// let class handle this
			$html .= (new FormField(array(
				'field' => $field,
				'value' => $this->values[$field['field_id']],
				'error' => $this->error,
				'lang' => $this->lang
			)))->render();
		}

		return $html;
	}

	public function submit($values) {
	// try to submit, and save errors to be used when rendering fields again

		// save values
		$this->values = $values;

		// find email field
		$email = $this->getEmailField();

		// try to submit to api
		$member = new Laposta_Member($this->list_id);
		$error = array();

		try {
			$result = $member->create(array(
				'ip' => $_SERVER['REMOTE_ADDR'],
				'email' => $values[$email['field_id']],
				'source_url' => $_SERVER['HTTP_REFERER'],
				'custom_fields' => $this->getFieldsWithValues(),
			));
		} catch (Exception $e) {

			$error = $e->json_body['error'];
			//print '<pre>';print_r($error);print '</pre>';
		} 


		// save error
		$this->error = $error;

		// return if an error was found
		return $error ? false : true;
	}

	private function getFields() {

		$fields = array();

		// initialize field api-object with list_id
		$field = new Laposta_Field($this->list_id);

		$result = array();
		try {
			// get all fields from this list
			// $result will contain een array with the response from the server
			$result = $field->all();

		} catch (Exception $e) {

			// you can use the information in $e to react to the exception
		}

		if ($result && $result['data']) {

			foreach($result['data'] as $field) {
				$fields[] = $field['field'];
			}
		}

		return $fields;
	}

	private function getFieldsWithValues() {
	// values for each field, formatted for api call

		$fields = array();
		foreach($this->fields as $field) {

			// trim { and } around tag
			$fields[trim($field['tag'], '{}')] = $this->values[$field['field_id']];
		}

		return $fields;
	}

	private function getEmailField() {
	// seach for email field

		foreach($this->fields as $field) {
			if ($field['is_email']) return $field;
		}

		return array();
	}
}
?>
