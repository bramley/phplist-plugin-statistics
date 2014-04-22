# Message Statistics Plugin #

## Installation ##

### Dependencies ###

This plugin is for phplist 3.0.0 and later.

Requires php version 5.2 or later.

Requires the Common Plugin to be installed. 

See <https://github.com/bramley/phplist-plugin-common>

### Set the plugin directory ###
You can use a directory outside of the web root by changing the definition of `PLUGIN_ROOTDIR` in config.php.
The benefit of this is that plugins will not be affected when you upgrade phplist.

### Install through phplist ###
The recommended way to install is through the Plugins page (menu Config > Plugins) using the package URL `https://github.com/bramley/phplist-plugin-statistics/archive/master.zip`.

In phplist releases 3.0.5 and earlier there is a bug that can cause a plugin to be incompletely installed on some configurations (<https://mantis.phplist.com/view.php?id=16865>). 
Check that these files are in the plugin directory. If not then you will need to install manually. The bug has been fixed in release 3.0.6.

* the file MessageStatisticsPlugin.php
* the directory MessageStatisticsPlugin

### Install manually ###
Download the plugin zip file from <https://github.com/bramley/phplist-plugin-statistics/archive/master.zip>

Expand the zip file, then copy the contents of the plugins directory to your phplist plugins directory.
This should contain

* the file MessageStatisticsPlugin.php
* the directory MessageStatisticsPlugin

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    2014-04-22  Fix problem of 'no campaigns found' after deleting list
    2014-01-27  Fix for Lists tab not showing correct latest campaign
                Select messages with sent, inprocess or suspended status 
    2014-01-27  On Messages tab order by sent date
    2014-01-25  Use Google Charts instead of Chart API
    2013-12-10  GitHub issue #3
    2013-08-26  Accumulated minor changes
    2013-04-22  Added README, changes to use the latest Common Plugin 
    2013-03-29  Initial version for phplist 2.11.x releases

