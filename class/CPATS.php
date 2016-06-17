<?php

// include required classes
include(dirname(__FILE__) . '/RMCARD.php');

/**
 * CyberPower ATS remote monitoring class
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 * 
 * This piece of code should extract the followind data:
 * 
 * - Temperature [summary_env_status.html]
 * - Humidity [summary_env_status.html]
 * - Status of outlets (on/off) [summary_status.html]
 * - Load [status_update.html]
 * - Wattage [status_update.html]
 * - Power [status_update.html]
 * - Energy [status_update.html]
 * - Power source data [status_update.html]
 * 
 * Usage:
 * 
 * include('class/CPATS.php');
 * $o = new CPATS('10.23.55.23', 'cyber', 'cyber');
 * $d = $o->getAllData();
 * var_dump($d);
 * unset($o);
 * 
 */
class CPATS extends RMCARD
{
	/**
	 * Does this ATS report humidity and temperature?
	 * @var boolean
	 */
	public $hasEnvironmentalData = false;

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
	 * Get list of outlets and their status
	 * @return array
	 */
	public function getOutletData()
	{
		$data = array();

		$this->hr->get('/summary_status.html');

		if ($this->hr->result) {
			// <span class="outletOnState">01</span>
			if (preg_match_all('/<span class="outlet(On|Off)State">([0-9]+)<\/span>/', $this->hr->result, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $value) {
					$data[$value[2]] = ($value[1] == "On") ? true : false;
				}
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
			'humidity' => false,
			'dewPoint' => false,
		);

		$this->hr->get('/summary_env_status.html');

		if ($this->hr->result) {
			// get temp: <td id="Temper" class="summaryTxt">75.0&deg;F</td>
			if (preg_match('/id="Temper"\s+[^>]+>(<font[^>]+>)?([0-9\.\-]+)(<\/font>)?&deg;(.)/', $this->hr->result, $matches)) {
				if ($matches[4] == 'F') {
					$data['temperature'] = ($matches[2] - 32) * (5/9);
				} else {
					$data['temperature'] = floatval($matches[2]);
				}
			}

			// get humidity: <td id="Humid" class="summaryTxt">70%RH</td>
			if (preg_match('/id="Humid"\s+[^>]+>(<font[^>]+>)?([0-9\.\-]+)/', $this->hr->result, $matches)) {
				$data['humidity'] = intval($matches[2]);
			}
			
			if ($data['temperature'] !== false && $data['humidity'] !== false) {
				$data['dewPoint'] = $data['temperature'] - ((100 - $data['humidity']) / 5);
				
				// we do have enviro data
				$this->hasEnvironmentalData = true;
			}
		}

		return $data;
	}
	
	/**
	 * Get ATS status data
	 * @return array
	 */
	public function getStatusData()
	{
		$data = array(
			'source' => array(
				'a' => array(
					'selected' => false,
					'preferred' => false,
					'voltage' => false,
					'frequency' => false,
					'status' => false,
				),
				'b' => array(
					'selected' => false,
					'preferred' => false,
					'voltage' => false,
					'frequency' => false,
					'status' => false,
				),
			),
			'phaseSynchronization' => false,
			'totalLoad' => false,
			'totalPower' => false,
			'peakLoad' => false,
			'energy' => false,
			'powerSupplyStatus' => false,
			'communicationStatus' => false,
		);

		$this->hr->get('/status_update.html');

		if ($this->hr->result) {
			// selected source
			if (preg_match('/selected source<\/span>\s*<span class="txt">source\s+([a-z])/i', $this->hr->result, $matches)) {
				$source = strtolower($matches[1]);
				$data['source'][$source]['selected'] = true;
			}
			
			// preferred source
			if (preg_match('/preferred source<\/span>\s*<span class="txt">source\s+([a-z])/i', $this->hr->result, $matches)) {
				$source = strtolower($matches[1]);
				$data['source'][$source]['preferred'] = true;
			}
			
			// source voltage
			if (preg_match('/source voltage \(a\/b\)<\/span>\s*<span class="txt">([0-9\.]+)\s*\/?\s*([0-9\.]*)/i', $this->hr->result, $matches)) {
				$data['source']['a']['voltage'] = $matches[1];
				$data['source']['b']['voltage'] = $matches[2];
			}
			
			// frequency
			if (preg_match('/source frequency \(a\/b\)<\/span>\s*<span class="txt">([0-9\.]+)\s*\/?\s*([0-9\.]*)/i', $this->hr->result, $matches)) {
				$data['source']['a']['frequency'] = $matches[1];
				$data['source']['b']['frequency'] = $matches[2];
			}
			
			// status
			if (preg_match('/source status \(a\/b\)<\/span>\s*<span class="txt">([a-z]+)\s*\/?\s*([a-z]*)/i', $this->hr->result, $matches)) {
				$data['source']['a']['status'] = ($matches[1] == 'OK') ? true : false;
				$data['source']['b']['status'] = ($matches[2] == 'OK') ? true : false;
			}

			// phase sync
			if (preg_match('/phase synchronization<\/span>\s*<span class="txt">([a-z]+)/i', $this->hr->result, $matches)) {
				$data['phaseSynchronization'] = ($matches[1] == 'No') ? false : true;
			}

			// total load
			if (preg_match('/total\s+load<\/span>\s*<span class="txt">([0-9\.]+)/i', $this->hr->result, $matches)) {
				$data['totalLoad'] = floatval($matches[1]);
			}

			// total power
			if (preg_match('/total\s+power<\/span>\s*<span class="txt">([0-9\.]+)/i', $this->hr->result, $matches)) {
				$data['totalPower'] = floatval($matches[1]);
			}

			// peak load
			if (preg_match('/peak\s+load<\/span>\s*<span class="l2b txt">([0-9\.]+)/i', $this->hr->result, $matches)) {
				$data['peakLoad'] = floatval($matches[1]);
			}

			// energy
			if (preg_match('/energy<\/span>\s*<span class="l2b txt">([0-9\.]+)/i', $this->hr->result, $matches)) {
				$data['energy'] = floatval($matches[1]);
			}

			// PS status
			if (preg_match('/power supply status<\/span>\s*<span class="txt">([a-z]+)/i', $this->hr->result, $matches)) {
				$data['powerSupplyStatus'] = ($matches[1] == 'OK') ? true : false;
			}

			// comm status
			if (preg_match('/communication status<\/span>\s*<span class="txt">([a-z]+)/i', $this->hr->result, $matches)) {
				$data['communicationStatus'] = ($matches[1] == 'OK') ? true : false;
			}
		}

		return $data;
	}
	
	/**
	 * Get all data at once
	 * @return array
	 */
	public function getAllData()
	{
		$data = array(
			'outlet' => $this->getOutletData(),
			'environmental' => $this->getEnvironmentalData()
		);

		$status = $this->getStatusData();

		return array_merge($data, $status);
	}
}