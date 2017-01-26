<?php
class FormField {

	private $field;
	private $value;
	private $lang;

	public function __construct($vars) {

		// this field's definition
		$this->field = $vars['field'];

		// current value
		$this->value = $vars['value'];

		// possible error
		$this->error = $vars['error'];

		// language
		$this->lang = $vars['lang'];
	}

	public function render() {

		$html = '<div class="field">';

		// label (name)
		$html .= '<div class="label">';
		$html .= '<label for="id-' . $this->field['field_id'] . '">';
		$html .= htmlspecialchars($this->field['name']);

		if ($this->field['required']) {
			$html .= '<span class="required">';
			$html .= '*';
			$html .= '</span>';
		}
		$html .= '</label>';
		$html .= '</div><!-- /.label -->';

		// show error, if present
		$html .= $this->getHtmlError();

		// input
		$html .= $this->getHtmlInput();

		$html .= '</div><!-- /.field -->';

		return $html;
	}

	private function getHtmlInput() {
	// an input element in the correct datatype, prefilled if needed

		if ($this->field['datatype'] == 'text') {
			$html = $this->getHtmlInputText();
		}

		else if ($this->field['datatype'] == 'numeric') {
			$html = $this->getHtmlInputNumeric();
		}

		else if ($this->field['datatype'] == 'date') {
			$html = $this->getHtmlInputDate();
		}

		else if ($this->field['datatype'] == 'select_single') {
			$html = $this->getHtmlInputSelectSingle();
		}

		else if ($this->field['datatype'] == 'select_multiple') {
			$html = $this->getHtmlInputSelectMulti();
		}

		return $html;
	}

	private function getHtmlInputText() {

		$html = '<input type="text" name="' . $this->field['field_id'] . '" id="id-' . $this->field['field_id'] . '"';
		if ($this->value) {
			$html .= ' value="' . htmlspecialchars($this->value) . '"';
		}
		$html .= '>';

		return $html;
	}

	private function getHtmlInputNumeric() {

		$html = '<input type="text" name="' . $this->field['field_id'] . '" id="id-' . $this->field['field_id'] . '"';
		if ($this->value) {
			$html .= ' value="' . htmlspecialchars($this->value) . '"';
		}
		$html .= '>';

		return $html;
	}

	private function getHtmlInputDate() {
	// you might want to use a datepicker here

		$html = '<input type="text" name="' . $this->field['field_id'] . '" id="id-' . $this->field['field_id'] . '" class="date"';
		if ($this->value) {
			$html .= ' value="' . htmlspecialchars($this->value) . '"';
		}
		$html .= '>';

		return $html;
	}

	private function getHtmlInputSelectSingle() {

		$html = '<select name="' . $this->field['field_id'] . '" id="id-' . $this->field['field_id'] . '">';
		$html .= '<option value="0">' . $this->lang['select_choose'] . '</option>';
                foreach($this->field['options'] as $option) {
                        $html .= '<option value="' . htmlspecialchars($option) . '"';
			if ($option == $this->value) {
				$html .= ' selected';
			}
			$html .= '>' . htmlspecialchars($option) . '</option>';
                }
		$html .= '</select>';

		return $html;
	}

	private function getHtmlInputSelectMulti() {

		$count = 0;
		$html = '';
		if ($this->field['options']) foreach($this->field['options'] as $option) {

			// unique id for each checkbox
			$el_id = 'id-' . $this->field['field_id'] . '-' . $count++;

			$html .= '<div class="cb">';
			$html .= '<input type="checkbox" name="' . $this->field['field_id'] . '[]" value="' . htmlspecialchars($option) . '" id="' . $el_id . '"';
			if (is_array($this->value) && in_array($option, $this->value)) {
				$html .= ' checked';
			}
			$html .= '>';
			$html .= '&nbsp;<label for="' . $el_id . '">' . htmlspecialchars($option) . '</label>';
			$html .= '</div>';
		}

		return $html;
	}

	private function getHtmlError() {
	// show error, if present for this field

		$html = '';

		if ($this->error['id'] == $this->field['field_id']) {

			$lang = $this->lang['errors'][$this->error['code']];
			if (!$lang) $lang = $this->lang['errors']['unknown'] . ' (' . $this->error['code'] . ')';

			$html .= '<div class="error">';
			$html .= $lang;
			$html .= '</div>';
		}

		return $html;
	}
}
?>
