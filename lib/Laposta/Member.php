<?php
class Laposta_Member extends Laposta_Resource {

	private $list_id;

	public function __construct($list_id) {

		// we need the list_id for each call
		$this->list_id = $list_id;
		parent::__construct(get_class());
	}

	public function get($member_id) {

		return parent::connect(array(
			'path' => array($member_id),
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

	public function update($member_id, $data) {

		// add list_id to data
		$data['list_id'] = $this->list_id;

		return parent::connect(array(
			'path' => array($member_id),
			'post' => $data
			)
		);
	}

	public function delete($member_id) {

		return parent::connect(array(
			'path' => array($member_id),
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
