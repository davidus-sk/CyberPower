<?php

include('HttpRequest.php');

/**
 * Class which manages connection to CyberPower remote management card.
 * 
 * This is not the best way of doing it, but the RMCARD offers no XML
 * or JSON feeds to pull data from.
 */
class RMCARD
{
	/**
	 * Address of the management card
	 * @var string
	 */
	private $host = null;

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
	 * Use MD5 for authentication
	 * @var boolean
	 */
	private $useMd5 = false;

	/**
	 * HttpRequest object
	 * @var HttpRequest
	 */
	protected $hr = null;

	/**
	 * Constructor
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($host, $username, $password, $useMd5 = false)
	{
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->useMd5 = $useMd5;

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

	/**
	 * Log into the RM Card web interface
	 * @return void
	 */
	private function login()
	{
		$this->hr = new HttpRequest($this->host);
		$this->hr->get('/login.cgi', array(
			'username' => $this->useMd5 ? md5($this->username) : $this->username,
			'password' => $this->useMd5 ? md5($this->password) : $this->password,
			'SelLan' => 0,
			'action' => 'LOGIN'
		));
	}

	/**
	 * Close the session so that next one can be opened
	 * @return void
	 */
	private function logout()
	{
		$this->hr->get('/logout.html');
	}
}
