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

## Alert!
This has been tested on Mac OS X 10.8.3 with the default PHP runtime environment with iStat Server 2.12
