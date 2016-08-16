<?php
namespace Laposta;

class Resource {

	protected $result;
	private $classname;

	public function __construct($classname) {

		$this->classname = $classname;
	}

	protected function connect($data = array()) {

		// request parts
		$path = isset($data['path']) ? (is_array($data['path']) ? $data['path'] : array()) : array();
		$parameters = isset($data['parameters']) ? (is_array($data['parameters']) ? $data['parameters'] : array()) : array();
		$post = isset($data['post']) ? (is_array($data['post']) ? $data['post'] : array()) : null;
		$method = isset($data['method']) ? $data['method'] : null;

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
		if (is_array($post)) {

			if ($post) $post = http_build_query($post);
			else $post = true; // empty post
		}

		return Request::connect(array(
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

		$resource = strtolower(str_replace(['Laposta\\', '_'], '', $this->classname)); // Remove _ (List_ class) and namespace

		return $resource;
	}
}
?>
