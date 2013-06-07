# Info
This is a collection of graphs from iStat Server 2's database for [Panic's Status Board](http://panic.com/statusboard/) app for iPad.

[Check out the blog post here.](http://www.yesdevnull.net/2013/05/istat-server-graphs-for-status-board/)

## Usage
1. Chuck this file anywhere on your server accessible by Apache (and the outside world, or just in your network)
2. Add a graph in Status Board with the URI ```/path/to/file/istat.php?data=xxx```

Data Types:
* ram_hour  : RAM usage for the last 60 minutes
* ram_day   : RAM usage for the last 24 hours
* cpu_hour  : CPU usage for the last 60 minutes
* cpu_day   : CPU usage for the last 24 hours
* load_hour : CPU load for the last 60 minutes
* load_day  : CPU load for the last 24 hours
* temp_hour : Temp sensors for the last 60 minutes
* temp_day  : Temp sensors for the last 24 hours

### CPU Graph Usage
There's an optional parameter called ```$cores``` that you can provide to better scale the graph.  By default, we assume that your machine is a dual-core machine, but you can override that using the ```$cores``` variable.  Simply add to the query string like such:

```&cores=4``` or ```&cores=8```

Remember, this defaults to 2, so if your machine is dual-core, don't worry about passing ```$cores``` as we assume dual-core.

### Temperature Sensor Usage
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

####Optional Temperature Scale
You can optionally specify the desired temperature scale (Celsius or Fahrenheit). If no scale is specified, the scale will default to Celsius. Simply add to the query string like below:

```&temp_unit=f``` or ```&temp_unit=c```

## Alert!
This has been tested on Mac OS X 10.8.3 with the default PHP runtime environment with iStat Server 2.12
