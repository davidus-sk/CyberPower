<?php
/**
 * Simple script to retrieve data from ATS unit
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 */

// include the class
include(dirname(__FILE__) . '/class/CPATS.php');

// connect to ATS and login
$ats = new CPATS('10.23.55.23', 'cyber', 'cyber');

// get all merged data
$data = $ats->getAllData();

// see the data
var_dump($data);

// destroy and logout from ATS
unset($ats);