<?php

include('class/RMCARD203.php');

$r = new RMCARD203('10.23.55.23', 'cyber', 'cyber');

$e = $r->getEnvironmentalData();
$o = $r->getOutletData();

var_dump($e);
var_dump($o);

unset($r);
