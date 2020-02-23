<?php
class Laposta_Report extends Laposta_Resource {

	public function __construct() {

		parent::__construct(get_class());
	}

	public function get($campaign_id) {

		return parent::connect(array(
			'path' => array($campaign_id)
			)
		);
	}

	public function all() {

		return parent::connect();
	}
}
?>
