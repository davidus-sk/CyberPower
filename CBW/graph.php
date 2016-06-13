<?php

// include class
include(dirname(__FILE__) . '/CBW.php');
include(dirname(__FILE__) . '/RRD.php');

$cbw = new CBW('192.168.42.21');

// get data
$data = $cbw->get1WireData(array(
	'VUE Temp' => 'F',
	'VUE Humidity' => '%RH',
	'JB Temp' => 'F',
	'VUE LED Temp' => 'F'
));

// init RRD class
$rrd = new RRD(dirname(__FILE__) . '/vue.rrd');

// create DB if needed
$rrd->create(array(
	array('name' => 'vueTemp', 'min' => -50, 'max' => 200),
	array('name' => 'vueHum', 'min' => 0, 'max' => 100),
	array('name' => 'jbTemp', 'min' => -50, 'max' => 200),
	array('name' => 'vueLedTemp', 'min' => -50, 'max' => 200),
));

// update DB
$rrd->update(array_map(function($a) {
	return empty($a['value']) ? 'U' : $a['value'];
}, $data));

// graph