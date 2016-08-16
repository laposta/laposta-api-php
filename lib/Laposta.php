<?php
namespace Laposta;

if (!function_exists('curl_init')) {
	throw new Exception('Laposta needs the CURL PHP extension.');
}

if (!function_exists('json_decode')) {
	throw new Exception('Laposta needs the JSON PHP extension.');
}

class Laposta {

	public static $apiKey;
	public static $https = true;
	public static $httpsDisableVerifyPeer = false;
	public static $apiBase = 'api.laposta.nl/v2';

	const VERSION = '1.5.0';

	public static function getApiKey() {
		return self::$apiKey;
	}

	public static function setApiKey($apiKey) {
		self::$apiKey = $apiKey;
	}

	public static function getProtocol() {
		return self::$https ? 'https' : 'http';
	}

	public static function setHttps($https) {
		self::$https = $https;
	}

	public static function setHttpsDisableVerifyPeer($disable) {
		self::$httpsDisableVerifyPeer = $disable;
	}

	public static function getHttpsDisableVerifyPeer() {
		return self::$httpsDisableVerifyPeer;
	}

	public static function getApiBase() {
		return self::getProtocol() . '://' . self::$apiBase;
	}
}
?>
