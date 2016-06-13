<?php

// include class
include(dirname(__FILE__) . '/CBW.php');
include(dirname(__FILE__) . '/RRD.php');

// connect to CBW module
$cbw = new CBW('192.168.42.21');

// get data
$data = $cbw->get1WireData(array(
	'VUE Temp' => '°F',
	'VUE Humidity' => '%RH',
	'JB Temp' => '°F',
	'VUE LED Temp' => '°F'
));

// init RRD class
$rrd = new RRD(dirname(__FILE__) . '/vue.rrd');

$fields = array(
	array('name' => 'vueTemp', 'min' => -50, 'max' => 200, 'label' => 'VUE Temp', 'unit' => '°F', 'color'=>'1f77b4', 'graph' => 'AREA'),
	array('name' => 'vueHum', 'min' => 0, 'max' => 100, 'label' => 'VUE Humidity', 'unit' => '%RH', 'color' => 'ff7f0e', 'graph' => 'LINE3'),
	array('name' => 'jbTemp', 'min' => -50, 'max' => 200, 'label' => 'JB Temp', 'unit' => '°F', 'color' => '2ca02c', 'graph' => 'AREA'),
	array('name' => 'vueLedTemp', 'min' => -50, 'max' => 200, 'label' => 'VUE LED Temp', 'unit' => '°F', 'color' => 'd62728', 'graph' => 'AREA'),
);

// create DB if needed
$rrd->create($fields);

// update DB
$rrd->update(array_map(function($a) {
	return empty($a['value']) ? 'U' : $a['value'];
}, $data));

// graph DB
$rrd->graph($fields, -86400, '/var/www/vue/graph_day.png');
$rrd->graph($fields, -604800, '/var/www/vue/graph_week.png');