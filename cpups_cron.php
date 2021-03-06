<?php
/**
 * Simple server shutdown script for CyberPower UPS
 *
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 *
 * This script should be scheduled to run frequently (every minute). It polls
 * CyberPower UPS for status and if it operates on batteries, it will try to
 * shutdown the server to prevent damage/data loss.
 *
 * Usage (add this line to your crontab file):
 *
 * * * * * * root php /path/to/cpups_cron.php -h host_or_ip -u username -p password
 */

// include the class
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'CPUPS.php');

// flag file
$flagFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cpups_cron.txt';

// get options
$options = getopt('h:u:p:t');

$host = empty($options['h']) ? false : $options['h'];
$username = empty($options['u']) ? false : $options['u'];
$password = empty($options['p']) ? false : $options['p'];
$timeLimit = empty($options['t']) ? 5 : $options['t'];

// check things
if (!$host || !$username || !$password) {
	die("Error: missing parameters!\n\nUsage:\n\t{$argv[0]} -h host -u username -p password [-t time_limit]\n");
}

// connect to UPS and login
$ups = new CPUPS($host, $username, $password);

// get data from UPS
$d = $ups->getAllData();

// clean up
unset($ups);

// are we running on batteries?
if (!empty($d['input']['status']) && ($d['input']['status'] == 'Blackout')) {

	// check the remaining runtime
	if ($d['battery']['runtime'] < $timeLimit) {
		$shutdown = false;
		
		// is this the second time we have battery online?
		if (file_exists($flagFile)) {
			// get stored tmestamp
			$time = file_get_contents($flagFile);

			// is the file fresh?
			if ((time() - $time) < 300) {
				$shutdown = true;
			}
		}

		// do we shutdown
		if ($shutdown) {
			// clean up
			unlink($flagFile);
			clearstatcache();

			// shutdown command must be running under privileged user account
			
			// do this if Windows
			if (stripos(PHP_OS, 'Win') === 0) {
				`shutdown -t 30 -s`;
			} else
			// do this if Linux
			if (stripos(PHP_OS, 'Linux') !== false)
				`shutdown -h now`;
			}
			// add more OS types if needed
			else {
				
			}
			
			die("Shutting the system down!\n");
		}

		// write out current time into flag file
		file_put_contents($flagFile, time());
	} else {

		// clean up
		if (file_exists($flagFile)) {
			unlink($flagFile);
			clearstatcache();
		}
	}
}
