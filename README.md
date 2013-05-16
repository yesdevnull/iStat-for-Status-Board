# Info
This is a collection of graphs from iStat Server 2's database for [Panic's Status Board](http://panic.com/statusboard/) app for iPad.

## Usage
1. Chuck this file anywhere on your server accessible by Apache
2. Add a graph in Status Board with the URI ```/path/to/file/istat.php?data=xxx```

Data Types:
* ram_day : RAM usage for the day
* cpu_day : CPU usage for the day

## Alert!
This has been tested on OS X Server 10.8.3 with the default PHP runtime environment with iStat Server 2.12
