<?php

// include required classes
include(dirname(__FILE__) . '/RMCARD.php');

/**
 * CyberPower UPS remote monitoring class
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 * 
 * This piece of code should extract status for:
 * 
 * - Battery
 * - Input
 * - Output
 * - Bypass
 * - Enviro
 * 
 * Usage:
 * 
 * include('class/CPUPS.php');
 * $o = new CPUPS('10.23.55.23', 'cyber', 'cyber', true);
 * $d = $o->getAllData();
 * var_dump($d);
 * unset($o);
 * 
 */
class CPUPS extends RMCARD
{
	/**
	 * Class constructor
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @param boolean $useMd5
	 */
	public function __construct($host, $username, $password, $useMd5 = false)
	{
		parent::__construct($host, $username, $password, $useMd5);
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * Get input data
	 * @return array
	 */
	public function getInputData()
	{
		$data = array(
			'status' => false,
			'voltage' => false,
			'frequency' => false,
		);

		$this->hr->get('/update_status.html');

		if ($this->hr->result) {
			// status
			if (preg_match('/input.*?status<\/span><[^>]+>(<[^>]+>)?([^<]+)/is', $this->hr->result, $matches)) {
				$data['status'] = $matches[2];
			}

			// voltage
			if (preg_match('/input.*?voltage<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['voltage'] = floatval($matches[2]);
			}

			// frequency
			if (preg_match('/input.*?frequency<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['frequency'] = floatval($matches[2]);
			}
		}

		return $data;
	}
	
	/**
	 * Get output data
	 * @return array
	 */
	public function getOutputData()
	{
		$data = array(
			'status' => false,
			'voltage' => false,
			'frequency' => false,
			'load' => false,
			'current' => false,
			
		);

		$this->hr->get('/update_status.html');

		if ($this->hr->result) {
			// status
			if (preg_match('/output.*?status<\/span><[^>]+>(<[^>]+>)?([^<]+)/is', $this->hr->result, $matches)) {
				$data['status'] = $matches[2];
			}

			// voltage
			if (preg_match('/output.*?voltage<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['voltage'] = floatval($matches[2]);
			}

			// frequency
			if (preg_match('/output.*?frequency<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['frequency'] = floatval($matches[2]);
			}
			
			// load
			if (preg_match('/output.*?load<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['load'] = floatval($matches[2]);
			}
			
			// current
			if (preg_match('/output.*?current<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['current'] = floatval($matches[2]);
			}
		}

		return $data;
	}
	
	/**
	 * Get battery data
	 * @return array
	 */
	public function getBatteryData()
	{
		$data = array(
			'status' => false,
			'capacity' => false,
			'runtime' => false,
		);

		$this->hr->get('/update_status.html');

		if ($this->hr->result) {
			// status
			if (preg_match('/battery.*?status<\/span><[^>]+>(<[^>]+>)?([^<]+)/is', $this->hr->result, $matches)) {
				$data['status'] = $matches[2];
			}

			// capacity
			if (preg_match('/battery.*?Remaining Capacity<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['capacity'] = floatval($matches[2]);
			}

			// runtime
			if (preg_match('/output.*?Remaining Runtime<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)/is', $this->hr->result, $matches)) {
				$data['frequency'] = floatval($matches[2]);
			}
		}

		return $data;
	}
	
	/**
	 * Get environmental data
	 * @return array
	 */
	public function getEnvironmentalData()
	{
		$data = array(
			'temperature' => false,
		);

		$this->hr->get('/summary_env_status.html');

		if ($this->hr->result) {
			// temperature
			if (preg_match('/system.*?temperature<\/span><[^>]+>(<[^>]+>)?([0-9\.]+)\s&deg;([a-z])/is', $this->hr->result, $matches)) {
				if ($matches[3] == 'F') {
					$data['temperature'] = ($matches[2] - 32) * (5/9);
				} else {
					$data['temperature'] = floatval($matches[2]);
				}
			}
		}
	}

	/**
	 * Get all data at once
	 * @return array
	 */
	public function getAllData()
	{
		return array(
			'input' => $this->getInputData(),
			'output' => $this->getOutputData(),
			'battery' => $this->getBatteryData(),
			'environmental' => $this->getEnvironmentalData()
		);
	}
}