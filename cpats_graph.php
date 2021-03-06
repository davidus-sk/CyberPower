<?php
/**
 * Simple script to produce ATS power and environmental graphs
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 */

// include classes
include(dirname(__FILE__) . '/class/CPATS.php');
include(dirname(__FILE__) . '/CBW/RRD.php');

// get options
$options = getopt('h:o:');

// list of ATS units to query
$atsList = empty($options['h']) ? false : explode(',', $options['h']);

// final graph (png) directory
$outputDirectory = empty($options['o']) ? false : rtrim($options['o'], "\n\t\s\r/");

// check input vars
if (empty($atsList) || !is_array($atsList) || empty($outputDirectory) || !is_dir($outputDirectory)) {
	die("Error: missing parameters!\n\nUsage:\n\t{$argv[0]} -h comma_separated_hosts -o output_directory\n");
}

// process all devices
foreach ($atsList as $atsIp) {
	// clean up the host
	$atsIp = trim($atsIp);

	// connect to ATS and login
	$ats = new CPATS($atsIp, 'cyber', 'cyber');

	// get all merged data
	$data = $ats->getAllData();

	// destroy and logout from ATS
	unset ($ats);

	// power ///////////////////////////////////////////////////////////////////

	// init RRD class
	$rrd = new RRD(dirname(__FILE__) . '/ats_' . $atsIp . '_power.rrd');

	$fields = array(
		array('name' => 'power', 'min' => 0, 'max' => 1000, 'label' => 'Power', 'unit' => 'W', 'color'=>'1f77b45A', 'graph' => 'AREA'),
		array('name' => 'energy', 'min' => 0, 'max' => 1000, 'label' => 'Energy', 'unit' => 'kWh', 'color' => 'ff7f0e5A', 'graph' => 'AREA'),
		array('name' => 'load', 'min' => 0, 'max' => 20, 'label' => 'Load', 'unit' => 'A', 'color' => '2ca02c', 'graph' => 'LINE3'),
	);

	// create DB if needed
	$rrd->create($fields);

	// update DB
	$rrd->update(array(
		$data['totalPower'],
		$data['energy'],
		$data['totalLoad']
	));

	// graph DB
	$rrd->graph($fields, -86400, $outputDirectory . '/graph_ats_' . $atsIp . '_power_day.png');
	$rrd->graph($fields, -604800, $outputDirectory . '/graph_ats_' . $atsIp . '_power_week.png');

	// environmental ///////////////////////////////////////////////////////////

	// init RRD class
	$rrd = new RRD(dirname(__FILE__) . '/ats_' . $atsIp . '_environmental.rrd');

	$fields = array(
		array('name' => 'temp', 'min' => -50, 'max' => 200, 'label' => 'Temperature', 'unit' => '°F', 'color'=>'1f77b45A', 'graph' => 'AREA'),
		array('name' => 'hum', 'min' => 0, 'max' => 100, 'label' => 'Humidity', 'unit' => '%%RH', 'color' => 'ff7f0e', 'graph' => 'LINE3'),
		array('name' => 'dew', 'min' => -50, 'max' => 200, 'label' => 'Dew Point', 'unit' => '°F', 'color' => '2ca02c', 'graph' => 'LINE3'),
	);

	// create DB if needed
	$rrd->create($fields);

	// update DB
	$rrd->update(array(
		$data['environmental']['temperature'] === false ? 'U' : ($data['environmental']['temperature'] * (9/5) + 32),
		$data['environmental']['humidity'] === false ? 'U' : $data['environmental']['humidity'],
		$data['environmental']['dewPoint'] === false ? 'U' : ($data['environmental']['dewPoint'] * (9/5) + 32),
	));

	// graph DB
	$rrd->graph($fields, -86400, $outputDirectory . '/graph_ats_' . $atsIp . '_environmental_day.png');
	$rrd->graph($fields, -604800, $outputDirectory . '/graph_ats_' . $atsIp . '_environmental_week.png');
}//foreach