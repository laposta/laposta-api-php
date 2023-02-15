<?php
class Laposta_Resource {

	const BULK_MODE_ADD = 'add';
	const BULK_MODE_ADD_AND_EDIT = 'add_and_edit';
	const BULK_MODE_EDIT = 'edit';

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
		$isJsonPost = isset($data[Laposta_Request::OPTION_IS_JSON_POST]) && $data[Laposta_Request::OPTION_IS_JSON_POST] === true;

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

			if ($post && !$isJsonPost) {
				$post = http_build_query($post);
			} elseif ($isJsonPost) {
				// also json encode empty value
				$post = json_encode($post);
			} else {
				// empty post
				$post = true;
			}
		}

		return Laposta_Request::connect(array(
			'url' => $url,
			'post' => $post,
			'method' => $method,
			Laposta_Request::OPTION_IS_JSON_POST => $isJsonPost,
		));
	}

	private function formatBaseUrl() {

		$url = Laposta::getApiBase() . '/' . $this->getResource();

		return $url;
	}

	private function getResource() {

		// remove 'Laposta_'
		$resource = strtolower(substr($this->classname, strpos($this->classname, '_') + 1));

		return $resource;
	}
}
?>
