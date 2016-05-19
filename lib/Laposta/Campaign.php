<?php
class Laposta_Campaign extends Laposta_Resource {

	public function __construct() {

		parent::__construct(get_class());
	}

	public function get($campaign_id, $endpoint_2 = '', $endpoint_3 = '') {

		return parent::connect(array(
			'path' => array($campaign_id, $endpoint_2, $endpoint_3)
			)
		);
	}

	public function create($data) {

		return parent::connect(array(
			'post' => $data
			)
		);
	}

	public function update($campaign_id, $data, $endpoint_2 = '', $endpoint_3 = '') {

		return parent::connect(array(
			'path' => array($campaign_id, $endpoint_2, $endpoint_3),
			'post' => $data
			)
		);
	}

	public function delete($campaign_id) {

		return parent::connect(array(
			'path' => array($campaign_id),
			'method' => 'DELETE'
			)
		);
	}

	public function all() {

		return parent::connect();
	}
}
?>
