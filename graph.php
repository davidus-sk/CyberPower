<?php

// include classes
include(dirname(__FILE__) . '/class/CPATS.php');
include(dirname(__FILE__) . '/CBW/RRD.php');

// connect to ATS and login
$ats = new CPATS('10.23.55.23', 'cyber', 'cyber');

// get all merged data
$data = $ats->getAllData();

unset ($ats);

// init RRD class
$rrd = new RRD(dirname(__FILE__) . '/ats_power.rrd');

$fields = array(
	array('name' => 'power', 'min' => 0, 'max' => 1000, 'label' => 'Power', 'unit' => 'W', 'color'=>'1f77b45A', 'graph' => 'AREA'),
	array('name' => 'energy', 'min' => 0, 'max' => 1000, 'label' => 'Energy', 'unit' => 'kWh', 'color' => 'ff7f0e', 'graph' => 'AREA'),
	array('name' => 'load', 'min' => 0, 'max' => 20, 'label' => 'Load', 'unit' => 'A', 'color' => '2ca02c5A', 'graph' => 'LINE3'),
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
