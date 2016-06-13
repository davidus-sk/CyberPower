<?php

class CBW
{
	/**
	 * IP/Hostname of CWB module
	 * @var string
	 */
	private $host = null;

	/**
	 * Class constructor
	 * @param string $host
	 */
	public function __construct($host)
	{
		$this->host = $host;
	}
	
	/**
	 * Class destructor
	 */
	public function __destruct() {
		;
	}
	
	/**
	 * Retrieve XML data from host
	 * @return boolean|SimpleXMLElement
	 */
	private function getXml()
	{
		$xmlString = file_get_contents('http://' . $this->host . '/state.xml');
		
		if (!empty($xmlString)) {
			return simplexml_load_string($xmlString);
		}
		
		return false;
	}
	
	/**
	 * Get sensor's numerical values
	 * @param array $fields
	 * @return array
	 */
	private function processXml($fields = array())
	{
		$data = array();
		
		// get XML data from remote host
		$xml = $this->getXml();

		if ($xml) {
			// loop over the four 1-wire sensors
			foreach ($fields as $key => $field) {
				if (filter_var($xml->{$field}, FILTER_SANITIZE_NUMBER_FLOAT)) {
					$data[$key] = filter_var($xml->{$field}, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION);
				} else {
					// some of these inputs tend to flip-flop, let's try this couple of times
					for ($j = 0; $j < 4; $j++) {
						$xml = $this->getXml();

						if ($xml && filter_var($xml->{$field}, FILTER_SANITIZE_NUMBER_FLOAT)) {
							$data[$key] = filter_var($xml->{$field}, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION);
							break;
						}//if
						
						usleep(50000);
					}//for
				}//if
			}//for
		}//if

		return $data;
	}

	/**
	 * Get sensor fields names and anotated
	 * $leged format:
	 * array('Inside temperature' => 'F', 'Outside temperature' => 'F')
	 * @param array $legend
	 * @return array
	 */
	public function get1WireData($legend = array())
	{
		$data = $this->processXml(array('sensor1', 'sensor2', 'sensor3', 'sensor4'));

		if (empty($legend)) {
			return $data;
		} else {
			$output = array();
			$i = 0;

			foreach ($legend as $name => $unit) {
				$output[] = array(
					'name' => $name,
					'value' => empty($data[$i]) ? null : $data[$i],
					'string' => empty($data[$i]) ? null : ($data[$i] . ' ' . $unit)
				);

				$i++;
			}

			return $output;
		}
	}
}