<?php
class FormList {

	private $list_id;
	private $label;
	private $values;

	public function __construct($vars) {

		// id of this list
		$this->list_id = $vars['list_id'];

		// label
		$this->label = $vars['label'];

		// the POST values
		$this->values = $vars['values'];
	}

	public function render() {

		$html = '<div class="list">';

		// checkbox
		$html .= '<input type="checkbox" name="list_ids[]" value="' . $this->list_id . '" id="list-' . $this->list_id . '"';
		if (isset($this->values['list_ids']) && in_array($this->list_id, $this->values['list_ids'])) {
			$html .= ' checked';
		}
		$html .= '>';

		// label
		$html .= '<label for="list-' . $this->list_id . '">';
		$html .= htmlspecialchars($this->label);
		$html .= '</label>';

		$html .= '</div><!-- /.list -->';

		return $html;
	}
}
?>
