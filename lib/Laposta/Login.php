<?php
class Laposta_Login extends Laposta_Resource {

	public function __construct() {

		parent::__construct(get_class());
	}

	public function get($login, $password) {

		return parent::connect(array(
			'parameters' => array('login' => $login, 'password' => $password)
			)
		);
	}
}
?>
