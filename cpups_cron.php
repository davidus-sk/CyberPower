<?php

// include the class
include('class/CPUPS.php');

// flag file
$fFile = '/tmp/cpups_cron.txt';

// connect to ATS and login
$r = new CPUPS('10.23.55.24', 'cyber', 'cyber');

// get data from UPS
$d = $r->getAllData();

// do we have runtime?
if (!empty($d['runtime'])) {

	// check the remaining runtime
	if ($d['runtime'] < 5) {
		$shutdown = false;
		
		if (file_exists($fFile)) {
			$time = file_get_contents($fFile);

			// is the file fresh?
			if ((time() - $time) < 300) {
				$shutdown = true;
			}
		}

		// do we shutdown
		if ($shutodwn) {
			// cleanup
			unlink($fFile);

			// script must be running as root
			`shutdown -h now`;
		}

		// wwrite out current time into flag file
		file_put_contents($fFile, time());
	}
}