# Message Statistics Plugin #

## Installation ##

### Dependencies ###

This plugin is for phplist 2.11.8 and later.

Requires php version 5.2 or later.

Requires the Common Plugin to be installed. 

See <https://github.com/bramley/phplist-plugin-common>

### Set the plugin directory ###
You can use a directory outside of the web root by changing the definition of `PLUGIN_ROOTDIR` in config.php.
The benefit of this is that plugins will not be affected when you upgrade phplist.

### Install through phplist ###
Install on the Plugins page (menu Config > Plugins) using the package URL `https://github.com/bramley/phplist-plugin-statistics/archive/master.zip`.

### Install manually ###
Download the plugin zip file from <https://github.com/bramley/phplist-plugin-statistics/archive/master.zip>

Expand the zip file, then copy the contents of the plugins directory to your phplist plugins directory.
This should contain

* the file MessageStatisticsPlugin.php
* the directory MessageStatisticsPlugin

## Version history ##

    version     Description
    2013-04-22  Added README, changes to use the latest Common Plugin 
    2013-03-29  Initial version for phplist 2.11.x releases

