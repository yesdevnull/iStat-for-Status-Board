# iStat for Status Board

**Note:** this is not maintained and has been archived.

## Info
This is a collection of graphs from iStat Server 2's database for [Panic's Status Board](http://panic.com/statusboard/) app for iPad.

[Check out the blog post here.](http://yesdevnull.net/2013/05/istat-server-graphs-for-status-board/)

### Usage
1. Chuck this file anywhere on your server accessible by Apache (and the outside world, or just in your network)
2. Add a graph in Status Board with the URI ```/path/to/file/istat.php?data=xxx```

Data Types:
* **cpu_day:** CPU usage for the last 24 hours
* **cpu_hour:** CPU usage for the last 60 minutes
* **ram_day:** RAM usage for the last 24 hours
* **ram_hour:** RAM usage for the last 60 minutes
* **io_day** Disk I/O for the last 24 hours
* **disk_month:** Disk usage for the last month
* **load_day:** CPU load for the last 24 hours
* **load_hour:** CPU load for the last 60 minutes
* **temp_day:** Temp sensors for the last 24 hours
* **temp_hour :** Temp sensors for the last 60 minutes

Also included is a graph modified to hide the X axis (timestamps).  To do this, simple add ```&hideXAxis``` anywhere in your query string.

#### CPU Graph Usage
There's an optional parameter called ```$cores``` that you can provide to better scale the graph.  By default, we assume that your machine is a dual-core machine, but you can override that using the ```$cores``` variable.  Simply add to the query string like such:

```&cores=4``` or ```&cores=8```

Remember, this defaults to 2, so if your machine is dual-core, don't worry about passing ```$cores``` as we assume dual-core.

#### Disk Usage Graph Usage
To monitor your disks, iStat for Status Board needs the UUID and name of each disk to monitor.  These should be put into an array in a file called ```istat_disks.php``` and stored in the same location as the rest of this code. 

Example:

```php
<?php

$monitoredDisks = array (
	'1' => array (
		'uuid' => 'YOUR_DISK_UUID' ,
		'name' => 'Macintosh HD' ,
	) ,
	'2' => array (
		'uuid' => 'ANOTHER_DISK_UUID' ,
		'name' => 'Another Disk Tracked By iStat Server' ,
	) ,
);
```

To get the UUID of your disk, go to Disk Utility, then select the volume and then click Info.  Copy the string next to **Universal Unique Identifier:**.  Name should be the same name as the volume, but you can call it something else if you want.

Once you've made that file, disks should then be added to the query string like so

```&disks=1``` or ```&disks=1,2```

Where the number(s) are the number you assigned the disks in ```istat_disks.php```.

#### Disk I/O Graph Usage
Like disk usage, disk I/O requires a ```istat_disks.php``` file with some information regarding your disks.  In the case of monitoring disk I/O, we need the serial number of the disk, along with a name.  iStat Server can currently only monitor internal disks.

Serial numbers for disks can be found in the System Information app (née System Profiler).  Open System Information then click on Serial-ATA.  On the right hand-side you'll see each internal HDD/SSD.  Click on each applicable disk you want to monitor and the serial number will be listed below.

Once you've got the serial number for each disk, enter the details in ```istat_disks.php``` like below:

```php
<?php

$ioDisks = array (
	'1' => array (
		'uuid' => 'DISK_SERIAL_NUMBER' ,
		'name' => 'BRAND_NAME_OF_DISK' ,
	) ,
	'2' => array (
		'uuid' => 'ANOTHER_DISK_SERIAL_NUMBER' ,
		'name' => 'ANOTHER_NAME_FOR_DISK' ,
	) ,
);
```

In my Mac mini Server I have two internal disks, see below for how I have my $ioDisks array configured (note that serial numbers have been obfuscated for warranty purposes):

```php
<?php

$ioDisks = array (
	'1' => array (
		'uuid' => 'OBFUSCATED' ,
		'name' => 'OCZ Vertex SSD' ,
	) ,
	'2' => array (
		'uuid' => 'OBFUSCATED' ,
		'name' => 'Hitachi HDD' ,
	) ,
);
```

Like the disk usage graph, you should add a ```disks``` var to your query string, example below:

```&disks=1``` or ```&disks=1,2```

Remember, multiple disks __MUST__ be comma delimited with no spaces.

#### Temperature Sensor Graph Usage
To use the temp sensor graphs you need to provide another parameter which should have a list of sensors
you'd like to get readings from the list below:

* __TC0D__: CPU A Temp
* __TC0H__: CPU A Heatsink
* __TC0P__: CPU A Proximity
* __TA0P__: Ambient Air 1
* __TA1P__: Ambient Air 2
* __TM0S__: Memory Slot 1
* __TMBS__: Memory Slot 2
* __TM0P__: Memory Slots Proximity
* __TH0P__: HDD Bay
* __TH1P__: HDD 2 Bay
* __TH2P__: HDD 3 Bay
* __TH3P__: HDD 4 Bay
* __TN0D__: Northbridge Diode
* __TN0P__: Northbridge Proximity
* __TI0P__: Thunderbolt Proximity 1
* __TI1P__: Thunderbolt Proximity 2
* __F0Ac__: Fan Speed (reported as actual speed / 100)

Once you've got the sensors you'd like to read, add them to the query string like below:

```&temps=TC0D``` or ```&temps=TC0D,TC0H``` or ```&temps=TC0D,TC0H,F0Ac```

Multiple temps __MUST__ be comma delimited with no spaces, otherwise they will be ignored.

##### Optional Temperature Scale
You can optionally specify the desired temperature scale (Celsius or Fahrenheit). If no scale is specified, the scale will default to Celsius. Simply add to the query string like below:

```&temp_unit=f``` or ```&temp_unit=c```

### Alert!
This has been tested on Mac OS X 10.8.3 with the default PHP runtime environment with iStat Server 2.12

