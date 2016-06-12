<?php

include('HttpRequest.php');

class RMCARD203
{
	/**
	 * Address of the management card
	 * @var string
	 */
	private $ipAddress = null;

	/**
	 * Username for the management card
	 * @var string
	 */
	private $username = null;

	/**
	 * Password for the management card
	 * @var string
	 */
	private $password = null;

	/**
	 * HttpRequest object
	 * @var HttpRequest
	 */
	private $hr = null;

	/**
	 * Constructor
	 * @param string $ipAddress
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($ipAddress, $username, $password)
	{
		$this->ipAddress = $ipAddress;
		$this->username = $username;
		$this->password = $password;

		// login
		$this->login();
	}
	
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->logout();
	}

	private function login()
	{
		$this->hr = new HttpRequest($this->ipAddress);
		$this->hr->get('/login.cgi', array(
			'username' => $this->username,
			'password' => $this->password,
			'SelLan' => 0,
			'action' => 'LOGIN'
		));
	}

	private function logout()
	{
		$this->hr->get('/logout.html');
	}

	public function getOutletData()
	{
		$data = array();

		$this->hr->get('/summary_status.html');

		if ($this->hr->result) {
			// <span class="outletOnState">01</span>
			if (preg_match_all('/<span class="outlet(On|Off)State">([0-9]+)</span>/', $this->hr->result, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $value) {
					$data[$value[2]] = ($value[1] == "On") ? true : false;
				}
			}
		}

		return $data;
	}

	public function getEnvironmentalData()
	{
		$data = array(
			'temperature' => false,
			'humidity' => false
		);

		$this->hr->get('/summary_env_status.html');

		if ($this->hr->result) {
			// get temp: <td id="Temper" class="summaryTxt">75.0&deg;F</td>
			if (preg_match('/id="Temper"\s+[^>]+>([0-9\.\-]+)&deg;(.)/', $this->hr->result, $matches)) {
				if ($matches[2] == 'F') {
					$data['temperature'] = ($matches[1] - 32) * (5/9);
				} else {
					$data['temperature'] = floatval($matches[1]);
				}
			}

			// get humidity: <td id="Humid" class="summaryTxt">70%RH</td>
			if (preg_match('/id="Humid"\s+[^>]+>([0-9\.\-]+)/', $this->hr->result, $matches)) {
				$data['humidity'] = intval($matches[1]);
			}
		}

		return $data;
	}
}
