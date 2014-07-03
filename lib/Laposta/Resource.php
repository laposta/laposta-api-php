<?php
class Laposta_Resource {

	protected $result;
	private $classname;

	public function __construct($classname) {

		$this->classname = $classname;

	}

	protected function connect($data = array()) {

		// request parts
		$path = is_array($data['path']) ? $data['path'] : array();
		$parameters = is_array($data['parameters']) ? $data['parameters'] : array();
		$post = is_array($data['post']) ? $data['post'] : array();
		$method = $data['method'];

		// start with base url
		$url = $this->formatBaseUrl();

		// add path
		if (count($path)) foreach($path as $item) {
			$url .= '/' . $item;
		}

		// add parameters to querystring
		if (count($parameters)) {
			$url .= '?' . http_build_query($parameters);
		}

		// build query for post
		if (count($post)) {
			$post = http_build_query($post);
		}

		return Laposta_Request::connect(array(
			'url' => $url, 
			'post' => $post,
			'method' => $method
			)
		);
	}

	private function formatBaseUrl() {

		$url = Laposta::getApiBase() . '/' . $this->getResource();

		return $url;
	}

	private function getResource() {

		$resource = strtolower(substr($this->classname, strpos($this->classname, '_') + 1));

		return $resource;
	}
}
?>
