<?php

include('HttpRequest.php');

class RMCARD203
{
	/**
	 * Address of the management card
	 * @var string
	 */
	private $ipAddress = null;

	private $username = null;
	
	
	private $password = null;
	
	/**
	 * HttpRequest object
	 * @var HttpRequest
	 */
	private $hr = null;

	/**
	 * Constructor
	 */
	public function __construct($ipAddress, $username, $password)
	{
			$this->ipAddress = $ipAddress;
			$this->username = $username;
			$this->password = $password;
			
			// login
			$this->login();
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
	
	public function getEnvironmentalData()
	{
		$this->hr->get('/summary_env_status.html');
	}
}