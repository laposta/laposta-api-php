<?php
class Laposta_Field extends Laposta_Resource {

	private $list_id;

	public function __construct($list_id) {

		// we need the list_id for each call
		$this->list_id = $list_id;
		parent::__construct(get_class());
	}

	public function get($field_id) {

		return parent::connect(array(
			'path' => array($field_id),
			'parameters' => array('list_id' => $this->list_id)
			)
		);
	}

	public function create($data) {

		// add list_id to data
		$data['list_id'] = $this->list_id;

		return parent::connect(array(
			'post' => $data
			)
		);
	}

	public function update($field_id, $data) {

		// add list_id to data
		$data['list_id'] = $this->list_id;

		return parent::connect(array(
			'path' => array($field_id),
			'post' => $data
			)
		);
	}

	public function delete($field_id) {

		return parent::connect(array(
			'path' => array($field_id),
			'parameters' => array('list_id' => $this->list_id),
			'method' => 'DELETE'
			)
		);
	}

	public function all() {

		return parent::connect(array(
			'parameters' => array('list_id' => $this->list_id)
			)
		);
	}
}
?>
