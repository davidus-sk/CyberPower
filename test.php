<?php

include('class/CPATS.php');

$r = new CPATS('10.23.55.23', 'cyber', 'cyber');

$e = $r->getAllData();

var_dump($e);

unset($r);
