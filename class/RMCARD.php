<?php

// include required classes
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
	 * @param boolean $useMd5
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
	 * /login.cgi?username=a47fd49bb3b8af7df55e61b0d406fcc8&password=a47fd49bb3b8af7df55e61b0d406fcc8&SelLan=0&action=LOGIN
	 * @return void
	 */
	private function login()
	{
		$this->hr = new HttpRequest($this->host);
		$this->hr->get('/login.cgi', array(
			'username' => $this->useMd5 ? $this->cipher($this->username) : $this->username,
			'password' => $this->useMd5 ? $this->cipher($this->password) : $this->password,
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

	/**
	 * CP tries to make things bit complicated
	 * @param string $str
	 * @return string
	 */
	private function cipher($str)
	{
		$this->hr->get('/login_counter.html');

		if (!empty($this->hr->result)) {
			if (preg_match('/<counter>([^<]+)<\/counter>/i', $this->hr->result, $matches)) {
				return md5($str . $matches[1]);
			}
		}

		return false;
	}
}
