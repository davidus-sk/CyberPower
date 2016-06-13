<?php

// include class
include(dirname(__FILE__) . '/CBW.php');

$cbw = new CBW('192.168.42.21');

// get data
$data = $cbw->get1WireData(array(
	'VUE Temp' => 'F',
	'VUE Humidity' => '%RH',
	'JB Temp' => 'F',
	'VUE LED Temp' => 'F'
));

var_dump($data);