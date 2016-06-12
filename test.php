<?php

include('class/RMCARD203.php');

$o = new RMCARD203('10.23.55.23', 'cyber', 'cyber');

$e = $o->getEnvironmentalData();

var_dump($e);

unset($o);
