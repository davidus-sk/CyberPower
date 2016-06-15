<?php

// include class
include(dirname(__FILE__) . '/CBW.php');
include(dirname(__FILE__) . '/RRD.php');

// connect to CBW module
$cbw = new CBW('192.168.42.21');

// get data from 1-wire sensors
$data = $cbw->get1WireData(array(
	'VUE Temp' => '°F',
	'VUE Humidity' => '%RH',
	'JB Temp' => '°F',
	'VUE LED Temp' => '°F'
));

// init RRD class
$rrd = new RRD(dirname(__FILE__) . '/vue_environmental.rrd');

// these are in order sensor 1..4
$fields = array(
	array('name' => 'vueTemp', 'min' => -50, 'max' => 200, 'label' => 'VUE Temperature', 'unit' => '°F', 'color'=>'1f77b45A', 'graph' => 'AREA'),
	array('name' => 'vueHum', 'min' => 0, 'max' => 100, 'label' => 'VUE Humidity', 'unit' => '%%RH', 'color' => 'ff7f0e', 'graph' => 'LINE3'),
	array('name' => 'jbAmb', 'min' => -50, 'max' => 200, 'label' => 'JB Ambient', 'unit' => '°F', 'color' => '2ca02c5A', 'graph' => 'AREA'),
	array('name' => 'vueLedTemp', 'min' => -50, 'max' => 200, 'label' => 'VUE LED Temperature', 'unit' => '°F', 'color' => 'd627285A', 'graph' => 'AREA'),
	array('name' => 'vueDewPoint', 'min' => -50, 'max' => 2000, 'label' => 'VUE Dew Point', 'unit' => '°F', 'color' => '9467bd', 'graph' => 'LINE3'),
);

// create DB if needed
$rrd->create($fields);

// add virtual fields
$vueTempCelsius = ($data[0]['value'] - 32) * (5/9);
$vueDewPointFahrenheit = ($vueTempCelsius - ((100 - $data[1]['value']) / 5)) * (9/5) + 32;
$data[] = array(
	'name' => 'VUE Dew Point',
	'value' => $vueDewPointFahrenheit,
	'string' => $vueDewPointFahrenheit . '°F'
);

// update DB
$rrd->update(array_map(function($a) {
	return empty($a['value']) ? 'U' : $a['value'];
}, $data));

// graph DB
$rrd->graph($fields, -86400, '/var/www/vue/graph_vue_environmental_day.png');
$rrd->graph($fields, -604800, '/var/www/vue/graph_vue_environmental_week.png');