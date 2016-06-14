<?php

// include classes
include(dirname(__FILE__) . '/class/CPATS.php');
include(dirname(__FILE__) . '/CBW/RRD.php');

// connect to ATS and login
$ats = new CPATS('10.23.55.23', 'cyber', 'cyber');

// get all merged data
$data = $ats->getAllData();

// destroy and logout from ATS
unset ($ats);

// power

// init RRD class
$rrd = new RRD(dirname(__FILE__) . '/ats_power.rrd');

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
$rrd->graph($fields, -86400, '/var/www/vue/graph_ats_power_day.png');
$rrd->graph($fields, -604800, '/var/www/vue/graph_ats_power_week.png');

// environmental

// init RRD class
$rrd = new RRD(dirname(__FILE__) . '/ats_environmental.rrd');

$fields = array(
	array('name' => 'temp', 'min' => -50, 'max' => 200, 'label' => 'Temperature', 'unit' => '°F', 'color'=>'1f77b45A', 'graph' => 'AREA'),
	array('name' => 'hum', 'min' => 0, 'max' => 100, 'label' => 'Humidity', 'unit' => '%%RH', 'color' => '2ca02c', 'graph' => 'LINE3'),
);

// create DB if needed
$rrd->create($fields);

// update DB
$rrd->update(array(
	$data['environmental']['temperature'] * (9/5) + 32,
	$data['environmental']['humidity']
));

// graph DB
$rrd->graph($fields, -86400, '/var/www/vue/graph_ats_environmental_day.png');
$rrd->graph($fields, -604800, '/var/www/vue/graph_ats_environmental_week.png');

