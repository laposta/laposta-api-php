<?php
Class Laposta_Request {

	public static function connect($data) {

		// result from server
		$response = Laposta_Util::connect(array(
			'url' => $data['url'], 
			'headers' => self::getHeaders(), 
			'api_key' => Laposta::getApiKey(), 
			'post' => $data['post'], 
			'method' => $data['method'],
			'httpsDisableVerifyPeer' => Laposta::getHttpsDisableVerifyPeer()
		));

		// check for CURL error
		if ($response['error']) {
			throw new Laposta_Error('Connection error: ' . $response['error_msg'], $response['status'], $response['body']);
		}

		// decode JSON
		$result = self::decode($response);

		// check for API errors
		if ($response['status'] < 200 || $response['status'] >= 300) {
			throw new Laposta_Error('API error: ' . $result['error']['message'], $response['status'], $response['body'], $result);
		}

		return $result;
	}

	private static function getHeaders() {

		$ua = array(
			'bindings_version' => Laposta::VERSION,
			'lang' => 'php',
			'lang_version' => phpversion(), 
			'uname' => php_uname()
		);

		$headers = array(
			'X-Laposta-Client-User-Agent: ' . json_encode($ua), 
			'User-Agent: laposta-php-' . Laposta::VERSION
		);

		return $headers;
	}

	private static function decode($response) {

		$result = json_decode($response['body'], true);

		// no problems decoding?
		if (!is_array($result)) {
			throw new Laposta_Error('Invalid response body from API', $response['status'], $response['body']);
		}

		return $result;
	}
}
?>
