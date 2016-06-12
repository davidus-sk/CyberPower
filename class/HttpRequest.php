<?php

class HttpRequest
{
	private $server = null;
	private $port = 80;
	private $protocol = null;
	private $baseUrl = null;

	/**
	 * Supported URL protocols
	 * @var array
	 */
	private $allowedProtocols = array('http', 'https');
	
	public $result = null;
	
	public function __construct($server, $port = 80, $protocol = 'http')
	{
		$this->server = $server;
		$this->port = (int)$port;
		$this->protocol = strtolower($protocol);
		
		// check if we have valid port number
		if ($port <= 0 || $port > 65535) {
			throw new Exception('Invalid port number: ' . $port);
		}
		
		// check if we have valid protocol
		if (!in_array($protocol, $this->allowedProtocols)) {
			throw new Exception('Unsupported protocol: ' . $protocol);
		}
	}

	private function createUrl($path = '/', $params = array())
	{
		// valid path has to start with /
		if (strpos($path, '/') === 0 && is_array($params)) {
			return $this->protocol . '://' . $this->server . ':' . $this->port . $path . (empty($params) ? null : '?' . http_build_query($params));
		}
		
		return false;
	}

	/**
	 * Perform HTTP GET request
	 *
	 * @param integer $path
	 * @param array $params
	 * @return bool
	 */
	public function get($path, $params = array())
	{
		if (!empty($path)) {
			// get path
			$url = $this->createUrl($path, $params);
			echo $url . "<br />";
			if ($url) {
				// do curl request
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie');
				curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie');
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt ($ch, CURLOPT_MAXREDIRS, 0);
				$result = curl_exec($ch);
				curl_close($ch);
var_dump($result);
				if ($result !== false) {
					$this->result = $result;

					return true;
				}
			}
		}

		return false;
	}
}
