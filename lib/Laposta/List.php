<?php
class Laposta_List extends Laposta_Resource {

	public function __construct() {

		parent::__construct(get_class());
	}

	public function get($list_id) {

		return parent::connect(array(
			'path' => array($list_id)
			)
		);
	}

	public function create($data) {

		return parent::connect(array(
			'post' => $data
			)
		);
	}

	public function update($list_id, $data) {

		return parent::connect(array(
			'path' => array($list_id),
			'post' => $data
			)
		);
	}

	public function delete($list_id, $endpoint_2 = '', $endpoint_3 = '') {

		return parent::connect(array(
			'path' => array($list_id, $endpoint_2, $endpoint_3),
			'method' => 'DELETE'
			)
		);
	}

	public function bulk($list_id, $data, $endpoint_2 = '', $endpoint_3 = '') {

		return parent::connect(array(
			'path' => array($list_id, $endpoint_2, $endpoint_3),
			'post' => $data,
			Laposta_Request::OPTION_IS_JSON_POST => true,
		));
	}

	public function all() {

		return parent::connect();
	}
}