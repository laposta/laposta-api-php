<?php
class Laposta_Campaign extends Laposta_Resource {

	public function __construct() {

		parent::__construct(get_class());
	}

	public function get($list_id) {

		return parent::connect(array(
			'path' => array($list_id)
			)
		);
	}

	public function all() {

		return parent::connect();
	}
}
?>
