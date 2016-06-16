<?php

// include required classes
include(dirname(__FILE__) . '/RMCARD.php');

/**
 * CyberPower UPS remote monitoring class
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 * 
 * This piece of code should extract status for:
 * 
 * - Battery
 * - Input
 * - Output
 * - Bypass
 * - Enviro
 * 
 * Usage:
 * 
 * include('class/CPUPS.php');
 * $o = new CPUPS('10.23.55.23', 'cyber', 'cyber');
 * $d = $o->getAllData();
 * var_dump($d);
 * unset($o);
 * 
 */
class CPUPS
{
	
}