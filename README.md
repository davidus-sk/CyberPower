# CyberPower UPS/ATS Remote Monitoring and Graphing

This project contains a handful of simple classes that give you automated remote access to CyberPower's
RMCARD interface. You will be able to retrieve system and environmental data
using PHP. You can use the included classes to automate monitoring and graphing
of collected data.

## ATS

`RMCARD.php` and `CPATS.php` enable you to remotely login into an ATS unit and "scrape"
system and environmental data from the RMCARD's output. A simple example:

```php
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
```

### Graphing with RRDTool

## UPS

`RMCARD.php` and `CPUPS.php` enable you to remotely login into a UPS unit and "scrape"
system and environmental data from the RMCARD's output. A simple example:

```php
// include the class
include(dirname(__FILE__) . '/class/CPUPS.php');

// connect to UPS and login
$ups = new CPUPS('10.23.55.23', 'cyber', 'cyber', true);

// get all merged data
$data = $ups->getAllData();

// see the data
var_dump($data);

// destroy and logout from UPS
unset($ups);
```

### Automatic server shutdown

File `cpups_cron.php` can be used to automatically shutdown your server when battery level
dips below a critical threshold and the system is running on batteries alone. This script should be
scheduled via CRON to execute periodically. It polls the UPS for status and when
battery operation is detected and remaining run time is below a certain value, a shutdown
command is issued on the host server. The script file should be scheduled as follows:

```
* * * * * root php /path/to/cpups_cron.php -h host_or_ip -u username -p password
```

You have the following configuration options available to you:

```
-h <host>
-u <username>
-p <password>
[-t <remaining time threshold in minutes>] this is optional and defaults to 5 minutes
```