<?php
class Form {

	private $lists;
	private $list_id;
	private $fields;
	private $lang;
	private $error;

	public function __construct($vars) {

		$this->lists = $vars['lists'];
		$this->lang = $vars['lang'];

		// take first list_id for convenience
		$this->list_id = $this->lists[0]['id'];

		// get fields
		$this->fields = $this->getFields();
	}

	public function renderForm($values = []) {
	// render form based on list info from api; uses values from POST to prefill

		// we should have fields
		if (!$this->fields) {
			return 'Sorry, the form cannot be shown.';
		}

		$html = '';

		// walk through fields
		foreach($this->fields as $field) {

			// seperate class
			$html .= (new FormField(array(
				'field' => $field,
				'value' => $values ? $values[$field['field_id']] : '',
				'error' => $this->error,
				'lang' => $this->lang
			)))->render();
		}

		return $html;
	}

	public function renderLists($values = []) {
	// render lists

		$html = '';

		if ($values && !$this->anyListSelected($values)) {
			$html .= '<div class="error">';
			$html .= $this->lang['no_list_selected'];
			$html .= '</div>';
		}

		// walk through lists
		foreach($this->lists as $list) {

			// seperate class
			$html .= (new FormList(array(
				'list_id' => $list['id'],
				'label' => $list['label'],
				'values' => $values
			)))->render();
		}

		return $html;
	}

	public function submit($values) {
	// try to submit, and save errors and values to be used when rendering fields again

		// we need at least one list selected
		if (!$this->anyListSelected($values)) {

			// stop here
			return false;
		}

		// find email field from main list; this should be handed separately to the api
		$email = $this->getEmail($values);

		// find and format the other fields
		$custom_fields = $this->getCustomFields($values);

		// try to submit to api; for each one of the lists
		$errors = false;
		foreach($this->lists as $list) {

			// should we post to this list?
			if (!$this->isListSelected($list['id'], $values)) continue;

			// yes; track if error occurs
			if (!$this->submitToList($list['id'], $email, $custom_fields)) {
				$errors = true;
			}

		}

		// return whether succeeded
		return !$errors;
	}

	private function submitToList($list_id, $email, $custom_fields) {

		$member = new Laposta_Member($list_id);
		$error = array();

		try {
			$result = $member->create(array(
				'ip' => $_SERVER['REMOTE_ADDR'],
				'email' => $email,
				'custom_fields' => $custom_fields,
 				'source_url' => $_SERVER['HTTP_REFERER']
			));

		} catch (Exception $e) {

			$error = $e->json_body['error'];
		} 

		// save error
		if (!$this->error) $this->error = $error;

		// return whether succeeded
		return $this->error ? false : true;
	}

	private function getFields() {

		$fields = array();

		// initialize field api-object with list_id
		$field = new Laposta_Field($this->list_id);

		try {
			// get all fields from this list
			// $result will contain een array with the response from the server
			$result = $field->all();

		} catch (Exception $e) {

			// you can use the information in $e to react to the exception
		}

		if ($result['data']) {

			foreach($result['data'] as $field) {
				$fields[] = $field['field'];
			}
		}

		return $fields;
	}

	private function getCustomFields($values) {
	// values for each field, formatted for api call

		$fields = array();
		foreach($this->fields as $field) {

			// trim { and } around tag
			$fields[trim($field['tag'], '{}')] = $values[$field['field_id']];
		}

		return $fields;
	}

	private function getEmail($values) {
	// seach for email field

		$email_field = [];
		foreach($this->fields as $field) {
			if ($field['is_email']) {
				return $values[$field['field_id']];
			}
		}

		return '';
	}

	private function anyListSelected($values) {
	// see if at least one list was selected

		if (!isset($values)) return false;
		if (!isset($values['list_ids'])) return false;

		return true;
	}

	private function isListSelected($list_id, $values) {
	// see if given list was selected

		if (!isset($values)) return false;
		if (!isset($values['list_ids'])) return false;

		return in_array($list_id, $values['list_ids']);
	}
}
?>
