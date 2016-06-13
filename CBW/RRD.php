<?php

class RRD
{
	/**
	 * RRD database file
	 * @var string
	 */
	private $filename = null;

	/**
	 * Class constructor
	 * @param string $filename
	 */
	public function __construct($filename) {
		$this->filename = $filename;
	}

	/**
	 * Create RRD database
	 * @param array $fields
	 * @throws Exception
	 */
	public function create($fields)
	{
		if (!empty($this->filename) && is_array($fields) && !empty($fields)) {
			if (!file_exists($this->filename)) {
				$command = 'rrdtool create ' . $this->filename . ' --step 60 ';

				foreach ($fields as $field) {
					$command .= 'DS:' . $field['name'] . ':GAUGE:120:' . $field['min'] . ':' . $field['max'] . ' ';
				}

				$command .= 'RRA:AVERAGE:0.5:1:1200 RRA:MIN:0.5:12:2400 RRA:MAX:0.5:12:2400 RRA:AVERAGE:0.5:12:2400';
				`$command`;
			}
		} else {
			throw new Exception('Required fields are missing.');
		}
	}

	/**
	 * Update RRD database
	 * @param array $data
	 * @throws Exception
	 */
	public function update($data)
	{
		if (is_array($data) && !empty($data)) {
			$command = 'rrdtool update ' . $this->filename . ' N:' . join(':', $data);
			`$command`;
		} else {
			throw new Exception('Required data is missing.');
		}
	}
	
	/**
	 * Generate graph from RRD database
	 * @param array $fields
	 * @throws Exception
	 */
	public function graph($fields)
	{
		if (is_array($fields) && !empty($fields)) {
			$command = 'rrdtool graph /var/www/html/graph.png -w 785 -h 120 -a PNG --slope-mode --start -604800 --end now --vertical-label "Temperature (°F)" ';
			$command .= join(' ', array_map(function($a) {
				return 'DEF:' . $a['name'] . '=' . $this->filename . ':' . $a['name'] . ':AVERAGE ' .
						'LINE1:' . $a['name'] . '#' . $a['color'] . ':"' . $a['label'] . '" GPRINT:'. $a['name'] . ':"%.2lf %S' . $a['unit'] . '" ';
			}, $fields));

			`$command`;
		} else {
			throw new Exception('Required fields are missing.');
		}
	}
}