<?php
/**
 * Simple script to retrieve data from UPS unit
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 */

// include the class
include(dirname(__FILE__) . '/class/CPUPS.php');

// connect to ATS and login
$ups = new CPUPS('10.23.55.27', 'cyber', 'cyber', true);

// get all merged data
$data = $ups->getAllData();

// see the data
var_dump($data);

// destroy and logout from ATS
unset($ups);