<?php

// include the class
include('class/CPATS.php');

// connect to ATS and login
$r = new CPATS('10.23.55.23', 'cyber', 'cyber');

// get all merged data
$e = $r->getAllData();

// see the data
var_dump($e);

// destroy and logout from ATS
unset($r);